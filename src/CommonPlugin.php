<?php

namespace Eclipse\Common;

use Eclipse\Common\Foundation\Plugins\Plugin;
use Eclipse\Common\Admin\Filament\Clusters\Settings;

class CommonPlugin extends Plugin
{
    /**
     * Cluster that is used for grouping app and plugin settings
     */
    protected ?string $settingsCluster = Settings::class;

    public function getSettingsCluster(): ?string
    {
        return $this->settingsCluster;
    }

    public function settingsCluster(?string $settingsCluster): self
    {
        $this->settingsCluster = $settingsCluster;

        return $this;
    }
}
