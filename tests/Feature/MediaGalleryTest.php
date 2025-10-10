<?php

use Eclipse\Common\Filament\Forms\Components\MediaGallery;
use Filament\Actions\Action;

test('media gallery can be configured with collection', function () {
    $field = MediaGallery::make('images')
        ->collection('test-images');

    expect($field->getCollection())->toBe('test-images');
});

test('media gallery can be configured with preview options', function () {
    $field = MediaGallery::make('images')
        ->mediaColumns(6)
        ->thumbnailHeight(200)
        ->lightbox(false)
        ->orderable(false);

    expect($field->getMediaColumns())->toBe(6)
        ->and($field->getThumbnailHeight())->toBe(200)
        ->and($field->hasLightbox())->toBeFalse()
        ->and($field->isDragReorderable())->toBeFalse();
});

test('media gallery can be configured with upload options', function () {
    $field = MediaGallery::make('images')
        ->maxFiles(5)
        ->maxFileSize(2048)
        ->allowUrlUploads()
        ->allowFileUploads()
        ->single()
        ->acceptedFileTypes(['image/jpeg', 'image/png']);

    expect($field->getMaxFiles())->toBe(5)
        ->and($field->getMaxFileSize())->toBe(2048)
        ->and($field->getAllowUrlUploads())->toBeTrue()
        ->and($field->getAllowFileUploads())->toBeTrue()
        ->and($field->isMultiple())->toBeFalse()
        ->and($field->getAcceptedFileTypes())->toBe(['image/jpeg', 'image/png']);
});

test('media gallery has preview and orderable methods', function () {
    $previewField = MediaGallery::make('images')->preview();
    $orderableField = MediaGallery::make('images')->orderable();

    expect($previewField->hasLightbox())->toBeTrue()
        ->and($orderableField->isDragReorderable())->toBeTrue();
});

test('media gallery has correct default values', function () {
    $field = MediaGallery::make('images');

    expect($field->hasLightbox())->toBeFalse()
        ->and($field->isDragReorderable())->toBeFalse()
        ->and($field->getMediaColumns())->toBe(4)
        ->and($field->getThumbnailHeight())->toBe(150)
        ->and($field->getAllowFileUploads())->toBeFalse()
        ->and($field->getAllowUrlUploads())->toBeFalse();
});

test('media gallery upload methods work', function () {
    $fileOnlyField = MediaGallery::make('images')->allowFileUploads();
    $urlOnlyField = MediaGallery::make('images')->allowUrlUploads();
    $bothField = MediaGallery::make('images')->allowUploads();

    expect($fileOnlyField->getAllowFileUploads())->toBeTrue()
        ->and($fileOnlyField->getAllowUrlUploads())->toBeFalse()
        ->and($urlOnlyField->getAllowFileUploads())->toBeFalse()
        ->and($urlOnlyField->getAllowUrlUploads())->toBeTrue()
        ->and($bothField->getAllowFileUploads())->toBeTrue()
        ->and($bothField->getAllowUrlUploads())->toBeTrue();
});

test('media gallery has action methods', function () {
    $field = MediaGallery::make('images');

    expect($field->getUploadAction())->toBeInstanceOf(Action::class)
        ->and($field->getUrlUploadAction())->toBeInstanceOf(Action::class)
        ->and($field->getEditAction())->toBeInstanceOf(Action::class)
        ->and($field->getDeleteAction())->toBeInstanceOf(Action::class)
        ->and($field->getCoverAction())->toBeInstanceOf(Action::class)
        ->and($field->getReorderAction())->toBeInstanceOf(Action::class);
});

test('media gallery supports closure configuration', function () {
    $field = MediaGallery::make('images')
        ->collection(fn () => 'dynamic-collection')
        ->maxFiles(fn () => 10);

    expect($field->getCollection())->toBe('dynamic-collection')
        ->and($field->getMaxFiles())->toBe(10);
});

test('media gallery upload action has correct properties', function () {
    $field = MediaGallery::make('images');
    $uploadAction = $field->getUploadAction();

    expect($uploadAction->getName())->toBe('upload')
        ->and($uploadAction->getLabel())->toBe('Upload Files')
        ->and($uploadAction->getIcon())->toBe('heroicon-o-arrow-up-tray')
        ->and($uploadAction->getColor())->toBe('primary');
});

test('media gallery url upload action has correct properties', function () {
    $field = MediaGallery::make('images');
    $urlUploadAction = $field->getUrlUploadAction();

    expect($urlUploadAction->getName())->toBe('urlUpload')
        ->and($urlUploadAction->getLabel())->toBe('Add from URL')
        ->and($urlUploadAction->getIcon())->toBe('heroicon-o-link')
        ->and($urlUploadAction->getColor())->toBe('gray');
});

test('media gallery delete action has correct properties', function () {
    $field = MediaGallery::make('images');
    $deleteAction = $field->getDeleteAction();

    expect($deleteAction->getName())->toBe('deleteImage')
        ->and($deleteAction->getLabel())->toBe('Delete')
        ->and($deleteAction->getColor())->toBe('danger');
});

test('media gallery cover action has correct properties', function () {
    $field = MediaGallery::make('images');
    $coverAction = $field->getCoverAction();

    expect($coverAction->getName())->toBe('setCover')
        ->and($coverAction->getLabel())->toBe('Set as Cover');
});

test('media gallery reorder action has correct properties', function () {
    $field = MediaGallery::make('images');
    $reorderAction = $field->getReorderAction();

    expect($reorderAction->getName())->toBe('reorder');
});

test('media gallery supports responsive columns', function () {
    $field = MediaGallery::make('images')
        ->mediaColumns([
            'default' => 2,
            'sm' => 3,
            'lg' => 4,
            'xl' => 6,
        ]);

    expect($field->getMediaColumns())->toBe([
        'default' => 2,
        'sm' => 3,
        'lg' => 4,
        'xl' => 6,
    ])
        ->and($field->getGridClasses())->toBe('eclipse-media-gallery-grid')
        ->and($field->getGridStyle())->toContain('grid-template-columns: repeat(2, 1fr)')
        ->and($field->getGridStyle())->toContain('@media (min-width: 640px)')
        ->and($field->getGridStyle())->toContain('@media (min-width: 1024px)');
});

test('media gallery columns method works with simple integer', function () {
    $field = MediaGallery::make('images')->mediaColumns(5);

    expect($field->getMediaColumns())->toBe(5)
        ->and($field->getGridClasses())->toBe('eclipse-media-gallery-grid')
        ->and($field->getGridStyle())->toBe('grid-template-columns: repeat(5, 1fr);');
});

test('media gallery has bulk delete action', function () {
    $field = MediaGallery::make('images');
    $bulkDeleteAction = $field->getBulkDeleteAction();

    expect($bulkDeleteAction->getName())->toBe('bulkDelete')
        ->and($bulkDeleteAction->getLabel())->toBe('Delete Selected')
        ->and($bulkDeleteAction->getColor())->toBe('danger')
        ->and($bulkDeleteAction->getIcon())->toBe('heroicon-o-trash')
        ->and($bulkDeleteAction->getModalHeading())->toBe('Delete Images')
        ->and($bulkDeleteAction->getModalSubmitActionLabel())->toBe('Delete Selected');
});
