<?php

namespace Eclipse\Common\Filament\Forms\Components\Concerns;

use Closure;

trait HasMediaPreview
{
    protected array|Closure $previewConversions = ['thumb', 'preview'];

    protected int|Closure $previewHeight = 200;

    protected int|Closure $previewWidth = 200;

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
}