<?php

namespace Eclipse\Common\Foundation\Plugins;

use Exception;
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
     * @throws Exception
     */
    public function register(Panel $panel): void
    {
        // Discover all classes, even if directories do not exists — Filament already checks and skips those
        $panel->discoverResources($this->getClassPath('Resources'), $this->getClassNamespace('Resources'));
        $panel->discoverPages($this->getClassPath('Pages'), $this->getClassNamespace('Pages'));
        $panel->discoverClusters($this->getClassPath('Clusters'), $this->getClassNamespace('Clusters'));
        $panel->discoverWidgets($this->getClassPath('Widgets'), $this->getClassNamespace('Widgets'));
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

    protected function getClassPath(string $classDir): string
    {
        return $this->getPath('src')."/Filament/$classDir";
    }

    protected function getClassNamespace(string $classDir): string
    {
        return "$this->pluginNamespace\\Filament\\$classDir";
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
