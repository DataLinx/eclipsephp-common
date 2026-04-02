<?php

namespace Eclipse\Common;

use Eclipse\Common\Admin\Filament\Clusters\Settings;
use Eclipse\Common\Exceptions\InvalidConfigurationException;
use Eclipse\Common\Foundation\Plugins\Plugin;
use Eclipse\Common\Helpers\LocalizationHelper;

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

    /**
     * Get a list of available locales for the application.
     * Returns an array of locale codes (as keys) and their corresponding names (as values).
     *
     * @throws InvalidConfigurationException
     */
    public function getAvailableLocales(): array
    {
        $config = config('eclipse-common.available_locales');

        if (is_array($config)) {
            $locales = $config;
        } elseif (is_callable($config)) {
            $locales = $config();
        } else {
            throw new InvalidConfigurationException('Configuration "eclipse-common.available_locales" must be an array or a callable that returns an array.');
        }

        if (empty($locales)) {
            throw new InvalidConfigurationException('Configuration "eclipse-common.available_locales" must contain at least one locale.');
        }

        return array_map(fn ($locale) => LocalizationHelper::getLanguageNameFromCode($locale, true), $locales);
    }
}
