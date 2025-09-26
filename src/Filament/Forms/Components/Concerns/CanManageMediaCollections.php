<?php

namespace Eclipse\Common\Filament\Forms\Components\Concerns;

use Closure;

trait CanManageMediaCollections
{
    protected string|Closure $collection = 'images';

    public function collection(string|Closure $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    public function getCollection(): string
    {
        return $this->evaluate($this->collection);
    }
}
