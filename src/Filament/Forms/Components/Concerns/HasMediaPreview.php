<?php

namespace Eclipse\Common\Filament\Forms\Components\Concerns;

use Closure;

trait HasMediaPreview
{
    protected array|Closure $previewConversions = ['thumb', 'preview'];

    protected int|Closure $previewHeight = 200;

    protected int|Closure $previewWidth = 200;

    protected int|Closure $thumbnailHeight = 150;

    protected bool|Closure $preview = true;

    public function previewConversions(array|Closure $conversions): static
    {
        $this->previewConversions = $conversions;

        return $this;
    }

    public function previewHeight(int|Closure $height): static
    {
        $this->previewHeight = $height;

        return $this;
    }

    public function previewWidth(int|Closure $width): static
    {
        $this->previewWidth = $width;

        return $this;
    }

    public function thumbnailHeight(int|Closure $height): static
    {
        $this->thumbnailHeight = $height;

        return $this;
    }

    public function preview(bool|Closure $preview = true): static
    {
        $this->preview = $preview;

        return $this;
    }

    public function getPreviewConversions(): array
    {
        return $this->evaluate($this->previewConversions);
    }

    public function getPreviewHeight(): int
    {
        return $this->evaluate($this->previewHeight);
    }

    public function getPreviewWidth(): int
    {
        return $this->evaluate($this->previewWidth);
    }

    public function getThumbnailHeight(): int
    {
        return $this->evaluate($this->thumbnailHeight);
    }

    public function getPreview(): bool
    {
        return $this->evaluate($this->preview);
    }
}