<?php

namespace Eclipse\Common\Foundation\Pages;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for List pages that use the Scout search package and configured driver
 */
trait HasScoutSearch
{
    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $this->applyColumnSearchesToTableQuery($query);

        if (filled($search = $this->getTableSearch())) {
            $query->whereIn('id', $this->getModel()::search($search)->keys());
        }

        return $query;
    }
}
