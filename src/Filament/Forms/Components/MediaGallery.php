<?php

namespace Eclipse\Common\Filament\Forms\Components;

use Closure;
use Eclipse\Common\Filament\Forms\Components\Concerns\CanManageMediaCollections;
use Eclipse\Common\Filament\Forms\Components\Concerns\HasMediaPreview;
use Eclipse\Common\Filament\Forms\Components\Concerns\HasMediaUploadOptions;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Concerns\HasLoadingMessage;
use Filament\Forms\Components\Concerns\HasNestedRecursiveValidationRules;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaGallery extends Field
{
    use CanManageMediaCollections;
    use HasExtraAlpineAttributes;
    use HasLoadingMessage;
    use HasMediaPreview;
    use HasMediaUploadOptions;
    use HasNestedRecursiveValidationRules;
    use HasPlaceholder;

    /**
     * @var view-string
     */
    protected string $view = 'eclipse-common::filament.forms.components.media-gallery';

    protected array $temporaryImages = [];

    protected array|Closure $imageConversions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureDefaultState();
        $this->configureStateLifecycle();
        $this->configureActions();
        $this->configureFormBinding();
    }

    protected function configureDefaultState(): void
    {
        $this->default([]);
    }

    protected function configureStateLifecycle(): void
    {
        $this->afterStateHydrated(function (Field $component) {
            if ($component->getRecord()) {
                $component->refreshState();
            }
            $this->cleanupOldTempFiles();
        });

        $this->afterStateUpdated(function (Field $component) {
            if ($component->getRecord()) {
                $component->refreshState();
            }
        });
    }

    protected function configureActions(): void
    {
        $this->registerActions([
            $this->getUploadAction(),
            $this->getUrlUploadAction(),
            $this->getEditAction(),
            $this->getDeleteAction(),
            $this->getBulkDeleteAction(),
            $this->getCoverAction(),
            $this->getReorderAction(),
        ]);
    }

    protected function configureFormBinding(): void
    {
        $this->dehydrated(fn (?Model $record): bool => ! $record?->exists);
        $this->saveRelationshipsUsing(fn (Field $component, ?Model $record) => $this->saveMediaRelationships($component, $record));
    }

    protected function saveMediaRelationships(Field $component, ?Model $record): void
    {
        if (! $record || ! $record->exists) {
            return;
        }

        $livewire = $component->getLivewire();
        if (method_exists($livewire, 'afterCreate') && property_exists($livewire, 'temporaryImages') && $livewire->temporaryImages !== null) {
            // Let the HandlesImageUploads trait handle this
            return;
        }

        $state = $component->getState();

        if (! $state || ! is_array($state)) {
            return;
        }

        $this->processStateItems($record, $state);
        $this->removeDeletedMedia($record, $state);
        $this->ensureSingleCoverImage($record);
        $this->cleanupOldTempFiles();
    }

    protected function processStateItems(Model $record, array $state): void
    {
        foreach ($state as $index => $item) {
            if (isset($item['id']) && $item['id']) {
                $this->updateExistingMedia($record, $item, $index);
            } else {
                $this->createNewMedia($record, $item, $index);
            }
        }
    }

    protected function updateExistingMedia(Model $record, array $item, int $index): void
    {
        $media = $record->getMedia($this->getCollection())->firstWhere('id', $item['id']);
        if ($media) {
            $media->setCustomProperty('name', $item['name'] ?? []);
            $media->setCustomProperty('description', $item['description'] ?? []);
            $media->setCustomProperty('is_cover', $item['is_cover'] ?? false);
            $media->setCustomProperty('position', $index);
            $media->save();
        }
    }

    protected function createNewMedia(Model $record, array $item, int $index): void
    {
        if (isset($item['temp_file'])) {
            $this->createMediaFromTempFile($record, $item, $index);
        } elseif (isset($item['temp_url'])) {
            $this->createMediaFromUrl($record, $item, $index);
        }
    }

    protected function createMediaFromTempFile(Model $record, array $item, int $index): void
    {
        $tempPath = storage_path('app/public/'.$item['temp_file']);
        if (file_exists($tempPath)) {
            $record->addMedia($tempPath)
                ->usingFileName($this->sanitizeFilename($item['file_name'] ?? basename($tempPath)))
                ->withCustomProperties($this->getMediaCustomProperties($item, $index))
                ->toMediaCollection($this->getCollection());

            @unlink($tempPath);
        }
    }

    protected function createMediaFromUrl(Model $record, array $item, int $index): void
    {
        try {
            $record->addMediaFromUrl($item['temp_url'])
                ->usingFileName($this->sanitizeFilename($item['file_name'] ?? basename($item['temp_url'])))
                ->withCustomProperties($this->getMediaCustomProperties($item, $index))
                ->toMediaCollection($this->getCollection());
        } catch (Exception $e) {
        }
    }

    protected function getMediaCustomProperties(array $item, int $index): array
    {
        return [
            'name' => $item['name'] ?? [],
            'description' => $item['description'] ?? [],
            'is_cover' => $item['is_cover'] ?? false,
            'position' => $index,
        ];
    }

    protected function removeDeletedMedia(Model $record, array $state): void
    {
        $existingIds = collect($state)->pluck('id')->filter()->toArray();
        $record->getMedia($this->getCollection())
            ->whereNotIn('id', $existingIds)
            ->each(fn ($media) => $media->delete());
    }

    public function getAvailableLocales(): array
    {
        if (class_exists(\Eclipse\Core\Models\Locale::class)) {
            return \Eclipse\Core\Models\Locale::getAvailableLocales()
                ->pluck('name', 'id')
                ->toArray();
        }

        return ['en' => 'English'];
    }

    public function imageConversions(array|Closure $conversions): static
    {
        $this->imageConversions = $conversions;

        return $this;
    }

    public function getImageConversions(): array
    {
        return $this->evaluate($this->imageConversions);
    }

    public function getUploadAction(): Action
    {
        return Action::make('upload')
            ->label('Upload Files')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->modalHeading('Upload Images')
            ->modalSubmitActionLabel('Upload')
            ->form([
                FileUpload::make('files')
                    ->label('Choose files')
                    ->multiple()
                    ->image()
                    ->acceptedFileTypes($this->getAcceptedFileTypes())
                    ->imagePreviewHeight('200')
                    ->required()
                    ->directory('temp-images')
                    ->visibility('public')
                    ->storeFiles(true)
                    ->preserveFilenames(),
            ])
            ->action(function (array $data): void {
                if (! isset($data['files'])) {
                    return;
                }

                $originalMemoryLimit = ini_get('memory_limit');
                ini_set('memory_limit', '192M');

                try {
                    $record = $this->getRecord();
                    $maxFiles = $this->getMaxFiles();

                    if (! $record) {
                        $currentState = $this->getState() ?: [];
                        $existingCount = count($currentState);
                        $maxPosition = count($currentState) - 1;
                        $allowedCount = $maxFiles ? max(0, $maxFiles - $existingCount) : count($data['files']);
                        $filesToProcess = array_slice($data['files'], 0, $allowedCount);

                        if ($allowedCount <= 0) {
                            Notification::make()
                                ->title('Maximum files limit reached')
                                ->body("You can only upload {$maxFiles} file(s) total.")
                                ->warning()
                                ->send();

                            return;
                        }

                        $initialStateCount = count($currentState);
                        $addedInThisBatch = 0;

                        foreach ($filesToProcess as $filePath) {
                            if (is_string($filePath)) {
                                $fullPath = storage_path('app/public/'.$filePath);

                                if (file_exists($fullPath)) {
                                    $tempId = 'temp_'.uniqid();
                                    $fileName = $this->sanitizeFilename(basename($filePath));

                                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                    $mimeType = match ($extension) {
                                        'jpg', 'jpeg' => 'image/jpeg',
                                        'png' => 'image/png',
                                        'gif' => 'image/gif',
                                        'webp' => 'image/webp',
                                        default => 'image/jpeg'
                                    };

                                    $currentState[] = [
                                        'id' => null,
                                        'temp_id' => $tempId,
                                        'temp_file' => $filePath,
                                        'uuid' => (string) Str::uuid(),
                                        'url' => \Storage::url($filePath),
                                        'thumb_url' => \Storage::url($filePath),
                                        'preview_url' => \Storage::url($filePath),
                                        'name' => [],
                                        'description' => [],
                                        'is_cover' => $initialStateCount === 0 && $addedInThisBatch === 0,
                                        'position' => ++$maxPosition,
                                        'file_name' => $fileName,
                                        'mime_type' => $mimeType,
                                        'size' => 0,
                                    ];

                                    $addedInThisBatch++;
                                }
                            }
                        }

                        $this->state($currentState);

                        if (function_exists('gc_collect_cycles')) {
                            gc_collect_cycles();
                        }

                        $uploadedCount = count($filesToProcess);
                        $rejectedCount = count($data['files']) - $uploadedCount;

                        if ($uploadedCount > 0) {
                            Notification::make()
                                ->title($uploadedCount.' image(s) added successfully')
                                ->success()
                                ->send();
                        }

                        if ($rejectedCount > 0) {
                            Notification::make()
                                ->title($rejectedCount.' image(s) rejected')
                                ->body("Maximum files limit ({$maxFiles}) reached.")
                                ->warning()
                                ->send();
                        }

                        return;
                    }

                    $existingCount = $record->getMedia($this->getCollection())->count();
                    $maxPosition = $record->getMedia($this->getCollection())->max(fn ($m) => $m->getCustomProperty('position', 0)) ?? -1;
                    $allowedCount = $maxFiles ? max(0, $maxFiles - $existingCount) : count($data['files']);
                    $filesToProcess = array_slice($data['files'], 0, $allowedCount);
                    $uploadCount = 0;

                    if ($allowedCount <= 0) {
                        Notification::make()
                            ->title('Maximum files limit reached')
                            ->body("You can only upload {$maxFiles} file(s) total.")
                            ->warning()
                            ->send();

                        return;
                    }

                    foreach ($filesToProcess as $filePath) {
                        if (is_string($filePath)) {
                            $fullPath = storage_path('app/public/'.$filePath);

                            if (file_exists($fullPath)) {
                                $record->addMedia($fullPath)
                                    ->usingFileName($this->sanitizeFilename(basename($filePath)))
                                    ->withCustomProperties([
                                        'name' => [],
                                        'description' => [],
                                        'is_cover' => $existingCount === 0 && $uploadCount === 0,
                                        'position' => ++$maxPosition,
                                    ])
                                    ->toMediaCollection($this->getCollection());

                                $uploadCount++;

                                @unlink($fullPath);
                            }
                        }
                    }

                    $this->refreshState();

                    $rejectedCount = count($data['files']) - $uploadCount;

                    if ($uploadCount > 0) {
                        Notification::make()
                            ->title($uploadCount.' image(s) uploaded successfully')
                            ->success()
                            ->send();
                    }

                    if ($rejectedCount > 0) {
                        Notification::make()
                            ->title($rejectedCount.' image(s) rejected')
                            ->body("Maximum files limit ({$maxFiles}) reached.")
                            ->warning()
                            ->send();
                    }
                } catch (Exception $e) {
                    Notification::make()
                        ->title('Upload failed')
                        ->body('An error occurred during image processing. Please try uploading fewer images at once.')
                        ->danger()
                        ->send();
                } finally {
                    ini_set('memory_limit', $originalMemoryLimit);
                }
            })
            ->modalWidth('lg')
            ->closeModalByClickingAway(false);
    }

    public function getUrlUploadAction(): Action
    {
        return Action::make('urlUpload')
            ->label('Add from URL')
            ->icon('heroicon-o-link')
            ->color('gray')
            ->modalHeading('Add Images from URLs')
            ->modalSubmitActionLabel('Add Images')
            ->form([
                Textarea::make('urls')
                    ->label('Image URLs')
                    ->placeholder("https://example.com/image1.jpg\nhttps://example.com/image2.jpg")
                    ->rows(6)
                    ->required()
                    ->helperText('Enter one URL per line. Only direct image URLs (jpg, png, gif, webp) are supported.'),
            ])
            ->modalWidth('2xl')
            ->action(function (array $data): void {
                if (! isset($data['urls'])) {
                    return;
                }

                $urls = array_filter(array_map('trim', explode("\n", $data['urls'])));
                $record = $this->getRecord();
                $maxFiles = $this->getMaxFiles();

                if (empty($urls)) {
                    Notification::make()
                        ->title('No URLs provided')
                        ->body('Please enter at least one URL.')
                        ->warning()
                        ->send();

                    return;
                }

                $validUrls = [];

                foreach ($urls as $url) {
                    if (filter_var(trim($url), FILTER_VALIDATE_URL)) {
                        $validUrls[] = trim($url);
                    }
                }

                if (empty($validUrls)) {
                    Notification::make()
                        ->title('No valid URLs found')
                        ->body('Please ensure URLs are properly formatted.')
                        ->danger()
                        ->send();

                    return;
                }

                if (! $record) {
                    $currentState = $this->getState() ?: [];
                    $existingCount = count($currentState);
                    $maxPosition = count($currentState) - 1;
                    $allowedCount = $maxFiles ? max(0, $maxFiles - $existingCount) : count($urls);
                    $urlsToProcess = array_slice($urls, 0, $allowedCount);
                    $successCount = 0;
                    $failedUrls = [];

                    if ($allowedCount <= 0) {
                        Notification::make()
                            ->title('Maximum files limit reached')
                            ->body("You can only upload {$maxFiles} file(s) total.")
                            ->warning()
                            ->send();

                        return;
                    }

                    foreach ($urlsToProcess as $url) {
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $tempId = 'temp_'.uniqid();
                            $currentState[] = [
                                'id' => null,
                                'temp_id' => $tempId,
                                'temp_url' => $url,
                                'uuid' => (string) Str::uuid(),
                                'url' => $url,
                                'thumb_url' => $url,
                                'preview_url' => $url,
                                'name' => [],
                                'description' => [],
                                'is_cover' => count($currentState) === 0,
                                'position' => ++$maxPosition,
                                'file_name' => $this->sanitizeFilename(basename($url)),
                                'mime_type' => 'image/*',
                                'size' => 0,
                            ];
                            $successCount++;
                        } else {
                            $failedUrls[] = $url;
                        }
                    }

                    $rejectedUrls = array_slice($urls, $allowedCount);
                    $failedUrls = array_merge($failedUrls, $rejectedUrls);

                    $this->state($currentState);

                    if ($successCount > 0) {
                        Notification::make()
                            ->title($successCount.' image(s) added successfully')
                            ->success()
                            ->send();
                    }

                    if (! empty($failedUrls)) {
                        $rejectedCount = count($rejectedUrls ?? []);
                        $invalidCount = count($failedUrls) - $rejectedCount;

                        $title = 'Some URLs failed';
                        $body = '';

                        if ($rejectedCount > 0) {
                            $body .= "{$rejectedCount} URL(s) rejected (limit reached). ";
                        }
                        if ($invalidCount > 0) {
                            $body .= "{$invalidCount} invalid URL(s). ";
                        }

                        Notification::make()
                            ->title($title)
                            ->body(trim($body))
                            ->warning()
                            ->send();
                    }

                    return;
                }

                $existingCount = $record->getMedia($this->getCollection())->count();
                $maxPosition = $record->getMedia($this->getCollection())->max(fn ($m) => $m->getCustomProperty('position', 0)) ?? -1;
                $allowedCount = $maxFiles ? max(0, $maxFiles - $existingCount) : count($urls);
                $urlsToProcess = array_slice($urls, 0, $allowedCount);
                $successCount = 0;
                $failedUrls = [];
                $invalidUrls = [];

                if ($allowedCount <= 0) {
                    Notification::make()
                        ->title('Maximum files limit reached')
                        ->body("You can only upload {$maxFiles} file(s) total.")
                        ->warning()
                        ->send();

                    return;
                }

                foreach ($urlsToProcess as $url) {
                    $url = trim($url);

                    if (! filter_var($url, FILTER_VALIDATE_URL)) {
                        $invalidUrls[] = $url;

                        continue;
                    }

                    try {
                        $fileName = $this->extractImageFileName($url);

                        $mediaFile = $record->addMediaFromUrl($url)
                            ->withCustomProperties([
                                'name' => [],
                                'description' => [],
                                'is_cover' => $existingCount === 0 && $successCount === 0,
                                'position' => ++$maxPosition,
                            ]);

                        if ($fileName) {
                            $mediaFile->usingFileName($fileName);
                        }

                        $mediaFile->toMediaCollection($this->getCollection());
                        $successCount++;
                    } catch (Exception $e) {
                        $failedUrls[] = $url;
                    }
                }

                $rejectedUrls = array_slice($urls, $allowedCount);

                $this->refreshState();
                $this->sendUrlUploadNotifications(
                    $successCount,
                    $failedUrls,
                    $invalidUrls,
                    $rejectedUrls
                );
            })
            ->modalWidth('2xl')
            ->closeModalByClickingAway(false);
    }

    protected function isValidImageUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        $path = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, $imageExtensions)) {
            return true;
        }

        $pathLower = strtolower($path);
        $imageKeywords = ['logo', 'image', 'photo', 'picture', 'img', 'avatar', 'banner', 'thumb'];

        foreach ($imageKeywords as $keyword) {
            if (str_contains($pathLower, $keyword)) {
                return true;
            }
        }

        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            parse_str($query, $params);
            $imageParams = ['format', 'type', 'ext', 'extension'];
            foreach ($imageParams as $param) {
                if (isset($params[$param]) && in_array(strtolower($params[$param]), $imageExtensions)) {
                    return true;
                }
            }
        }

        return true;
    }

    protected function validateUrlAccessibility(string $url): bool
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'HEAD',
                    'timeout' => 5,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'follow_location' => true,
                    'max_redirects' => 3,
                ],
                'https' => [
                    'method' => 'HEAD',
                    'timeout' => 5,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'follow_location' => true,
                    'max_redirects' => 3,
                    'verify_peer' => false,
                    'verify_host' => false,
                ],
            ]);

            $headers = @get_headers($url, true, $context);

            if (! $headers) {
                $getContext = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 5,
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'follow_location' => true,
                        'max_redirects' => 3,
                    ],
                    'https' => [
                        'method' => 'GET',
                        'timeout' => 5,
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'follow_location' => true,
                        'max_redirects' => 3,
                        'verify_peer' => false,
                        'verify_host' => false,
                    ],
                ]);

                $headers = @get_headers($url, true, $getContext);
            }

            if (! $headers) {
                return false;
            }

            $statusLine = $headers[0] ?? '';
            $isSuccessful = str_contains($statusLine, '200') ||
                str_contains($statusLine, '302') ||
                str_contains($statusLine, '301') ||
                str_contains($statusLine, '304');

            if (! $isSuccessful) {
                return false;
            }

            $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? $headers['Content-type'] ?? '';
            if (is_array($contentType)) {
                $contentType = end($contentType);
            }

            if (empty($contentType)) {
                return true;
            }

            return str_starts_with(strtolower($contentType), 'image/');
        } catch (Exception $e) {
            return true;
        }
    }

    protected function extractImageFileName(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $filename = basename($path);

        if (! str_contains($filename, '.')) {
            $extension = $this->getMimeTypeFromUrl($url) === 'image/jpeg' ? 'jpg' : 'png';
            $filename = $filename.'.'.$extension;
        }

        return $this->sanitizeFilename($filename);
    }

    protected function getMimeTypeFromUrl(string $url): string
    {
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            default => 'image/jpeg'
        };
    }

    protected function sendUrlUploadNotifications(int $successCount, array $failedUrls, array $invalidUrls, array $rejectedUrls): void
    {
        if ($successCount > 0) {
            Notification::make()
                ->title("{$successCount} image(s) added successfully")
                ->success()
                ->send();
        }

        $totalFailed = count($failedUrls) + count($invalidUrls) + count($rejectedUrls);

        if ($totalFailed > 0) {
            $messages = [];

            if (! empty($failedUrls)) {
                $messages[] = count($failedUrls).' URL(s) could not be downloaded';
            }

            if (! empty($invalidUrls)) {
                $messages[] = count($invalidUrls).' invalid URL(s) (not direct image links)';
            }

            if (! empty($rejectedUrls)) {
                $messages[] = count($rejectedUrls).' URL(s) rejected (file limit reached)';
            }

            Notification::make()
                ->title('Some images failed to upload')
                ->body(implode(', ', $messages).'.')
                ->warning()
                ->send();
        }
    }

    public function getReorderAction(): Action
    {
        return Action::make('reorder')
            ->action(function (array $arguments): void {
                if (! isset($arguments['items'])) {
                    return;
                }

                $newOrder = $arguments['items'];
                $record = $this->getRecord();

                if (! $record) {
                    $state = $this->getState();
                    $orderedState = [];

                    foreach ($newOrder as $position => $uuid) {
                        $item = collect($state)->firstWhere('uuid', $uuid);
                        if ($item) {
                            $item['position'] = $position;
                            $orderedState[] = $item;
                        }
                    }

                    $this->state($orderedState);

                    Notification::make()
                        ->title('Images reordered successfully')
                        ->success()
                        ->send();

                    return;
                }

                $record->load('media');
                $mediaCollection = $record->getMedia($this->getCollection());

                foreach ($newOrder as $position => $uuid) {
                    $media = $mediaCollection->firstWhere('uuid', $uuid);
                    if ($media) {
                        $media->setCustomProperty('position', $position);
                        $media->save();
                    }
                }

                $record->load('media');

                $this->refreshState();

                Notification::make()
                    ->title('Images reordered successfully')
                    ->success()
                    ->send();
            })
            ->livewireClickHandlerEnabled(false);
    }

    public function getEditAction(): Action
    {
        return Action::make('editImage')
            ->label('Edit Image')
            ->modalHeading('Edit Image Details')
            ->modalSubmitActionLabel('Save Changes')
            ->form(function (array $arguments) {
                $args = $arguments['arguments'] ?? $arguments;
                $uuid = $args['uuid'] ?? null;
                $state = $this->getState();
                $image = collect($state)->firstWhere('uuid', $uuid);

                if (! $image) {
                    return [];
                }

                // Get locales directly from database to avoid any caching issues
                $locales = [];
                if (class_exists(\Eclipse\Core\Models\Locale::class)) {
                    $locales = \Eclipse\Core\Models\Locale::getAvailableLocales()
                        ->pluck('name', 'id')
                        ->toArray();
                }
                if (empty($locales)) {
                    $locales = ['en' => 'English'];
                }

                $fields = [];

                $fields[] = Placeholder::make('preview')
                    ->label('')
                    ->content(function () use ($image) {
                        return view('eclipse-common::components.media-preview', [
                            'url' => $image['preview_url'] ?? $image['url'],
                            'filename' => $image['file_name'],
                        ]);
                    });

                $defaultLocale = array_key_first($locales);
                $fields[] = Select::make('locale')
                    ->label('Language')
                    ->options($locales)
                    ->default($defaultLocale)
                    ->live()
                    ->afterStateUpdated(function ($state, $set) use ($image) {
                        $set('name', $image['name'][$state] ?? '');
                        $set('description', $image['description'][$state] ?? '');
                    });

                $fields[] = TextInput::make('name')
                    ->label('Name')
                    ->default($image['name'][$defaultLocale] ?? '');

                $fields[] = Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->default($image['description'][$defaultLocale] ?? '');

                return $fields;
            })
            ->action(function (array $data, array $arguments): void {
                $args = $arguments['arguments'] ?? $arguments;
                $uuid = $args['uuid'] ?? null;

                if (! $uuid) {
                    return;
                }

                $record = $this->getRecord();
                $locale = $data['locale'] ?? array_key_first($this->getAvailableLocales());

                if (! $record) {
                    $state = $this->getState();
                    $imageIndex = collect($state)->search(fn ($item) => $item['uuid'] === $uuid);

                    if ($imageIndex !== false) {
                        $state[$imageIndex]['name'][$locale] = $data['name'] ?? '';
                        $state[$imageIndex]['description'][$locale] = $data['description'] ?? '';

                        $this->state($state);

                        Notification::make()
                            ->title('Image details updated')
                            ->success()
                            ->send();
                    }

                    return;
                }

                $media = $record->getMedia($this->getCollection())->firstWhere('uuid', $uuid);
                if ($media) {
                    $nameTranslations = $media->getCustomProperty('name', []);
                    $descriptionTranslations = $media->getCustomProperty('description', []);

                    $nameTranslations[$locale] = $data['name'] ?? '';
                    $descriptionTranslations[$locale] = $data['description'] ?? '';

                    $media->setCustomProperty('name', $nameTranslations);
                    $media->setCustomProperty('description', $descriptionTranslations);
                    $media->save();

                    $this->refreshState();

                    Notification::make()
                        ->title('Image details updated')
                        ->success()
                        ->send();
                }
            })
            ->modalWidth('lg');
    }

    public function getCoverAction(): Action
    {
        return Action::make('setCover')
            ->label('Set as Cover')
            ->requiresConfirmation()
            ->modalHeading('Set as Cover Image')
            ->modalDescription('This image will be used as the main product image.')
            ->modalSubmitActionLabel('Set as Cover')
            ->action(function (array $arguments): void {
                $args = $arguments['arguments'] ?? $arguments;
                $uuid = $args['uuid'] ?? null;

                if (! $uuid) {
                    return;
                }

                $record = $this->getRecord();

                if (! $record) {
                    $state = $this->getState();

                    $newState = collect($state)->map(function ($item) use ($uuid) {
                        $item['is_cover'] = $item['uuid'] === $uuid;

                        return $item;
                    })->toArray();

                    $this->state($newState);

                    Notification::make()
                        ->title('Cover image updated')
                        ->success()
                        ->send();

                    return;
                }

                $record->getMedia($this->getCollection())->each(function ($media) {
                    $media->setCustomProperty('is_cover', false);
                    $media->save();
                });

                $targetMedia = $record->getMedia($this->getCollection())->firstWhere('uuid', $uuid);
                if ($targetMedia) {
                    $targetMedia->setCustomProperty('is_cover', true);
                    $targetMedia->save();
                }

                $this->refreshState();

                Notification::make()
                    ->title('Cover image updated')
                    ->success()
                    ->send();
            });
    }

    protected function mediaToArray(Media $media): array
    {
        return [
            'id' => $media->id,
            'uuid' => $media->uuid,
            'url' => $media->getUrl(),
            'thumb_url' => $media->getUrl('thumb'),
            'preview_url' => $media->getUrl('preview'),
            'name' => $media->getCustomProperty('name', []),
            'description' => $media->getCustomProperty('description', []),
            'is_cover' => $media->getCustomProperty('is_cover', false),
            'position' => $media->getCustomProperty('position', 0),
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
        ];
    }

    public function refreshState(): void
    {
        $record = $this->getRecord();
        if (! $record) {
            $this->state([]);

            return;
        }

        $record->load('media');

        $media = $record->getMedia($this->getCollection())
            ->map(fn (Media $media) => $this->mediaToArray($media))
            ->sortBy('position')
            ->values()
            ->toArray();

        $this->state($media);
    }

    protected function ensureSingleCoverImage(Model $record): void
    {
        $coverMedia = $record->getMedia($this->getCollection())
            ->filter(fn ($media) => $media->getCustomProperty('is_cover', false));

        if ($coverMedia->count() > 1) {
            $coverMedia->skip(1)->each(function ($media) {
                $media->setCustomProperty('is_cover', false);
                $media->save();
            });
        }

        if ($coverMedia->count() === 0 && $record->getMedia($this->getCollection())->count() > 0) {
            $firstMedia = $record->getMedia($this->getCollection())->first();
            $firstMedia->setCustomProperty('is_cover', true);
            $firstMedia->save();
        }
    }

    protected function sanitizeFilename(string $filename): string
    {
        $pathInfo = pathinfo($filename);
        $name = $pathInfo['filename'] ?? 'image';
        $extension = isset($pathInfo['extension']) ? '.'.$pathInfo['extension'] : '';

        $sanitizedName = Str::slug($name, '-');

        if (empty($sanitizedName)) {
            $sanitizedName = 'image-'.time();
        }

        return $sanitizedName.$extension;
    }

    protected function cleanupOldTempFiles(): void
    {
        $tempDir = storage_path('app/public/temp-images');
        if (! file_exists($tempDir)) {
            return;
        }

        $files = glob($tempDir.'/*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file) && $now - filemtime($file) >= 86400) {
                @unlink($file);
            }
        }
    }

    public function getBulkDeleteAction(): Action
    {
        return Action::make('bulkDelete')
            ->label('Delete Selected')
            ->color('danger')
            ->icon('heroicon-o-trash')
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading('Delete Images')
            ->modalDescription('Are you sure you want to delete the selected images? This action cannot be undone.')
            ->modalSubmitActionLabel('Delete Selected')
            ->modalIcon('heroicon-o-trash')
            ->modalIconColor('danger')
            ->action(function (array $arguments): void {
                $args = $arguments['arguments'] ?? $arguments;
                $uuids = $args['uuids'] ?? [];

                if (empty($uuids)) {
                    Notification::make()
                        ->title('No images selected')
                        ->body('Please select images to delete.')
                        ->warning()
                        ->send();

                    return;
                }

                $record = $this->getRecord();
                $deletedCount = 0;
                $hadCover = false;

                if (! $record) {
                    $state = $this->getState();
                    $newState = [];

                    foreach ($state as $item) {
                        $uuid = $item['uuid'] ?? null;

                        if (in_array($uuid, $uuids)) {
                            if ($item['is_cover'] ?? false) {
                                $hadCover = true;
                            }

                            if (isset($item['temp_file'])) {
                                $tempPath = storage_path('app/public/'.$item['temp_file']);
                                if (file_exists($tempPath)) {
                                    @unlink($tempPath);
                                }
                            }

                            $deletedCount++;
                        } else {
                            $newState[] = $item;
                        }
                    }

                    if ($hadCover && count($newState) > 0) {
                        $newState[0]['is_cover'] = true;
                    }

                    $this->state($newState);

                    Notification::make()
                        ->title($deletedCount.' image(s) removed')
                        ->success()
                        ->send();

                    return;
                }

                $mediaCollection = $record->getMedia($this->getCollection());
                $mediaToDelete = [];

                foreach ($uuids as $uuid) {
                    $media = $mediaCollection->firstWhere('uuid', $uuid);
                    if ($media) {
                        $mediaToDelete[] = $media;
                        if ($media->getCustomProperty('is_cover', false)) {
                            $hadCover = true;
                        }
                    }
                }

                foreach ($mediaToDelete as $media) {
                    $media->delete();
                    $deletedCount++;
                }

                if ($deletedCount > 0) {
                    $record->load('media');

                    if ($hadCover) {
                        $remainingMedia = $record->getMedia($this->getCollection());
                        if ($remainingMedia->count() > 0) {
                            $firstMedia = $remainingMedia->first();
                            $firstMedia->setCustomProperty('is_cover', true);
                            $firstMedia->save();
                        }
                    }

                    $this->refreshState();

                    Notification::make()
                        ->title($deletedCount.' image(s) deleted')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('No images were deleted')
                        ->warning()
                        ->send();
                }
            });
    }

    public function getDeleteAction(): Action
    {
        return Action::make('deleteImage')
            ->label('Delete')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Delete Image')
            ->modalDescription('Are you sure you want to delete this image? This action cannot be undone.')
            ->modalSubmitActionLabel('Delete')
            ->action(function (array $arguments): void {
                $args = $arguments['arguments'] ?? $arguments;
                $uuid = $args['uuid'] ?? null;

                if (! $uuid) {
                    return;
                }

                $record = $this->getRecord();

                if (! $record) {
                    $state = $this->getState();
                    $imageIndex = collect($state)->search(fn ($item) => $item['uuid'] === $uuid);

                    if ($imageIndex !== false) {
                        $wasCover = $state[$imageIndex]['is_cover'] ?? false;

                        if (isset($state[$imageIndex]['temp_file'])) {
                            $tempPath = storage_path('app/public/'.$state[$imageIndex]['temp_file']);
                            if (file_exists($tempPath)) {
                                @unlink($tempPath);
                            }
                        }

                        $newState = collect($state)->reject(fn ($item) => $item['uuid'] === $uuid)->values()->toArray();

                        if ($wasCover && count($newState) > 0) {
                            $newState[0]['is_cover'] = true;
                        }

                        $this->state($newState);

                        Notification::make()
                            ->title('Image removed')
                            ->success()
                            ->send();
                    }

                    return;
                }

                $media = $record->getMedia($this->getCollection())->firstWhere('uuid', $uuid);

                if (! $media) {
                    Notification::make()
                        ->title('Could not find image to delete')
                        ->warning()
                        ->send();

                    return;
                }

                $wasCover = $media->getCustomProperty('is_cover', false);

                $media->delete();

                $record->load('media');

                if ($wasCover) {
                    $remainingMedia = $record->getMedia($this->getCollection());
                    if ($remainingMedia->count() > 0) {
                        $firstMedia = $remainingMedia->first();
                        $firstMedia->setCustomProperty('is_cover', true);
                        $firstMedia->save();
                    }
                }

                $this->refreshState();

                Notification::make()
                    ->title('Image deleted')
                    ->success()
                    ->send();
            });
    }
}
