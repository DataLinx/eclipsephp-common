<?php

namespace Eclipse\Common\Filament\Forms\Components\Concerns;

use Closure;

trait HasMediaPreview
{
    protected bool|Closure $hasLightbox = false;

    protected bool|Closure $hasCoverImageSelection = true;

    protected bool|Closure $isDragReorderable = false;

    protected int|Closure $thumbnailHeight = 150;

    protected array|string|int|null $mediaColumns = 4;

    public function lightbox(bool|Closure $condition = true): static
    {
        $this->hasLightbox = $condition;

        return $this;
    }

    public function preview(): static
    {
        $this->hasLightbox = true;

        return $this;
    }

    public function coverImageSelection(bool|Closure $condition = true): static
    {
        $this->hasCoverImageSelection = $condition;

        return $this;
    }

    public function orderable(bool|Closure $condition = true): static
    {
        $this->isDragReorderable = $condition;

        return $this;
    }

    public function thumbnailHeight(int|Closure $height): static
    {
        $this->thumbnailHeight = $height;

        return $this;
    }

    public function mediaColumns(array|string|int|null $columns = 2): static
    {
        $this->mediaColumns = $columns;

        return $this;
    }

    public function hasLightbox(): bool
    {
        return $this->evaluate($this->hasLightbox);
    }

    public function hasCoverImageSelection(): bool
    {
        return $this->evaluate($this->hasCoverImageSelection);
    }

    public function isDragReorderable(): bool
    {
        return $this->evaluate($this->isDragReorderable);
    }

    public function getThumbnailHeight(): int
    {
        return $this->evaluate($this->thumbnailHeight);
    }

    public function getMediaColumns(?string $breakpoint = null): array|string|int|null
    {
        $columns = $this->evaluate($this->mediaColumns);

        if ($breakpoint && is_array($columns)) {
            return $columns[$breakpoint] ?? null;
        }

        return $columns;
    }

    public function getGridStyle(): string
    {
        $columns = $this->getMediaColumns();

        if (is_array($columns)) {
            $default = $columns['default'] ?? 4;
            $css = "grid-template-columns: repeat({$default}, 1fr);";

            $breakpoints = [
                'sm' => '640px',
                'md' => '768px',
                'lg' => '1024px',
                'xl' => '1280px',
                '2xl' => '1536px',
            ];

            foreach ($columns as $breakpoint => $count) {
                if ($breakpoint === 'default' || ! isset($breakpoints[$breakpoint])) {
                    continue;
                }

                $css .= " @media (min-width: {$breakpoints[$breakpoint]}) { grid-template-columns: repeat({$count}, 1fr); }";
            }

            return $css;
        }

        $columnCount = $columns ?? 4;

        return "grid-template-columns: repeat({$columnCount}, 1fr);";
    }

    public function getGridClasses(): string
    {
        return 'grid gap-3';
    }
}
