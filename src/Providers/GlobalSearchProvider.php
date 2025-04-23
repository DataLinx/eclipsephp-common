<?php

namespace Eclipse\Common\Providers;

use Filament\Facades\Filament;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Illuminate\Database\Eloquent\Model;

/**
 * Global search provider that uses Laravel Scout for resources/models that implement it
 */
class GlobalSearchProvider implements \Filament\GlobalSearch\Contracts\GlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $builder = GlobalSearchResults::make();

        foreach (Filament::getResources() as $resource) {
            if (! $resource::canGloballySearch()) {
                continue;
            }

            if (method_exists($resource::getModel(), 'search')) {
                // Use Scout search method
                $search = $resource::getModel()::search($query);

                $resourceResults = $search
                    ->get()
                    ->map(function (Model $record) use ($resource): ?GlobalSearchResult {
                        $url = $resource::getGlobalSearchResultUrl($record);

                        if (blank($url)) {
                            return null;
                        }

                        return new GlobalSearchResult(
                            title: $resource::getGlobalSearchResultTitle($record),
                            url: $url,
                            details: $resource::getGlobalSearchResultDetails($record),
                            actions: $resource::getGlobalSearchResultActions($record),
                        );
                    })
                    ->filter();
            } else {
                // Fallback to standard Filament search
                $resourceResults = $resource::getGlobalSearchResults($query);
            }

            if (! $resourceResults->count()) {
                continue;
            }

            $builder->category($resource::getPluralModelLabel(), $resourceResults);
        }

        return $builder;
    }
}
