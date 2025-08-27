<?php

namespace Eclipse\Common\Foundation\Plugins;

use Filament\Panel;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class Plugin implements \Filament\Contracts\Plugin
{
    /**
     * @var string Absolute path to the plugin directory
     */
    protected string $basePath;

    /**
     * @var string Namespace of the plugin (e.g. `\Eclipse\Cms`)
     */
    protected string $pluginNamespace;

    /**
     * @var string ID of the plugin (e.g. `eclipse-cms`)
     */
    protected string $id;

    public function __construct()
    {
        $reflection = new ReflectionClass(static::class);

        // Auto-detect plugin attributes
        $this->basePath = dirname($reflection->getFileName(), 2);
        $this->pluginNamespace = $reflection->getNamespaceName();
        $this->id = Str::of($this->pluginNamespace)->replace('\\', '-')->lower();
    }

    /**
     * Register any plugin services.
     *
     * @throws \Exception
     */
    public function register(Panel $panel): void
    {
        $panelName = ucfirst($panel->getId());

        // Discover all classes, even if directories do not exists — Filament already checks and skips those
        $panel->discoverResources($this->getPanelPath($panelName, 'Resources'), $this->getPanelNamespace($panelName, 'Resources'));
        $panel->discoverPages($this->getPanelPath($panelName, 'Pages'), $this->getPanelNamespace($panelName, 'Pages'));
        $panel->discoverClusters($this->getPanelPath($panelName, 'Clusters'), $this->getPanelNamespace($panelName, 'Clusters'));
        $panel->discoverWidgets($this->getPanelPath($panelName, 'Widgets'), $this->getPanelNamespace($panelName, 'Widgets'));
    }

    /**
     * Bootstrap any plugin services.
     */
    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Get the absolute path to the plugin directory. If the `$path` parameter is provided, it is appended to the path.
     */
    public function getPath(?string $path = null): string
    {
        return $this->basePath.($path ? "/$path" : '');
    }

    /**
     * Get the absolute path to the panel directory for the specified class.
     *
     * @param  string  $panelName  Panel name (e.g. `Admin`)
     * @param  string  $classDir  Class dir (e.g. `Resource`)
     * @return string Absolute path for the specified class (e.g. `/app/vendor/eclipsephp/cms-plugin/src/Admin/Filament/Resource`)
     */
    protected function getPanelPath(string $panelName, string $classDir): string
    {
        return $this->getPath('src')."/$panelName/Filament/$classDir";
    }

    /**
     * Get the full panel namespace for the specified class.
     *
     * @param  string  $panelName  Panel name (e.g. `Admin`)
     * @param  string  $classDir  Class dir (e.g. `Resource`)
     * @return string Full namespace for the specified class (e.g. `Eclipse\Cms\Admin\Resource`)
     */
    protected function getPanelNamespace(string $panelName, string $classDir): string
    {
        return "$this->pluginNamespace\\$panelName\\Filament\\$classDir";
    }

    /**
     * Get plugin ID (e.g. `eclipse-cms`)
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Create plugin instance
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Get plugin instance
     */
    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
