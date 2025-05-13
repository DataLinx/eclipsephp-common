<?php

namespace Eclipse\Common;

use RuntimeException;

class Package extends \Spatie\LaravelPackageTools\Package
{
    /**
     * Auto-load the settings class and settings migrations
     *
     * @param  string  $path  Directory of the settings class in your `src` folder, defaults to `Settings`
     */
    public function hasSettings(string $path = 'Settings'): static
    {
        // Add the settings path to the config
        $settings_path = $this->basePath($path);

        if (file_exists($settings_path)) {
            $paths = config('settings.auto_discover_settings', []);
            $paths[] = $settings_path;
            config(['settings.auto_discover_settings' => $paths]);
        } else {
            throw new RuntimeException('Settings path does not exist: '.$settings_path);
        }

        // Add settings migrations
        $migrations_path = $this->rootPath('database/settings');

        if (file_exists($migrations_path)) {
            $migrations = config('settings.migrations_paths', []);

            $migrations[] = $migrations_path;

            config(['settings.migrations_paths' => $migrations]);
        } else {
            throw new RuntimeException('Settings migrations path does not exist: '.$migrations_path);
        }

        return $this;
    }

    /**
     * Get path to the package root directory.
     *
     * If a directory is specified, the full path to the directory is returned.
     */
    public function rootPath(?string $directory = null): string
    {
        if (empty($directory)) {
            $directory = '../';
        } else {
            $directory = '../'.$directory;
        }

        return $this->basePath($directory);
    }
}
