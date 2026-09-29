<?php

namespace Eclipse\Common\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class Settings extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog6Tooth;

    public static function getNavigationLabel(): string
    {
        return __('eclipse-common::nav.settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('eclipse-common::nav.configuration');
    }
}
