<?php

namespace Eclipse\Common\Admin\Filament\Clusters;

use Filament\Clusters\Cluster;

class Settings extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationLabel(): string
    {
        return __('eclipse-common::nav.settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('eclipse-common::nav.configuration');
    }
}
