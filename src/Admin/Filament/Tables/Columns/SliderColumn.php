<?php

namespace Eclipse\Common\Admin\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Model;

class SliderColumn extends ImageColumn
{
    protected mixed $titleCallback = null;

    protected mixed $linkCallback = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extraImgAttributes(function (Model $record, $column): array {
            $imageUrls = $this->getImageUrls($column);

            if (empty($imageUrls)) {
                return [];
            }

            $lightboxData = [];
            foreach ($imageUrls as $imageUrl) {
                $lightboxData[] = [
                    'url' => $imageUrl,
                    'title' => $this->getTitle($record),
                    'link' => $this->getLink($record),
                    'filename' => basename($imageUrl),
                ];
            }

            return [
                'class' => 'cursor-pointer image-preview-trigger hover:opacity-75 transition-opacity',
                'onclick' => 'event.stopPropagation(); return false;',
                'data-lightbox-config' => json_encode($lightboxData),
            ];
        });
    }

    public function title(string|Closure $title): static
    {
        $this->titleCallback = $title;

        return $this;
    }

    public function link(string|Closure $link): static
    {
        $this->linkCallback = $link;

        return $this;
    }

    protected function getImageUrls($column): array
    {
        $imageUrls = $column->getState();

        if (! is_array($imageUrls)) {
            $imageUrls = $imageUrls ? [$imageUrls] : [];
        }

        return array_filter($imageUrls, function ($url) {
            if (! filled($url)) {
                return false;
            }

            return ! $this->isPlaceholderImage($url);
        });
    }

    protected function isPlaceholderImage(string $url): bool
    {
        $placeholderPatterns = [
            'data:image/svg+xml;base64,',
        ];

        foreach ($placeholderPatterns as $pattern) {
            if (str_contains(strtolower($url), strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    protected function getTitle(Model $record): string
    {
        if ($this->titleCallback) {
            return $this->evaluate($this->titleCallback, ['record' => $record]);
        }

        foreach (['name', 'title'] as $attr) {
            if (isset($record->{$attr})) {
                $value = $record->{$attr};

                return is_array($value) ? ($value[app()->getLocale()] ?? reset($value)) : $value;
            }
        }

        return class_basename($record).' #'.$record->getKey();
    }

    protected function getLink(Model $record): ?string
    {
        if ($this->linkCallback) {
            return $this->evaluate($this->linkCallback, ['record' => $record]);
        }

        return null;
    }
}
