<?php

namespace Eclipse\Common\Foundation\Testing;

use Eclipse\Common\Workbench\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Base test case for Eclipse packages, which can be extended in other packages' tests
 */
abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    protected (Model&Authenticatable)|null $user = null;

    protected function setUp(): void
    {
        // Always show errors when testing
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // Increase memory limit for tests
        ini_set('memory_limit', '512M');

        parent::setUp();

        $this->withoutVite();
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $token = getenv('TEST_TOKEN') ?: 'default';
        $path = $app->storagePath("framework/views/$token");

        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $app['config']->set('view.compiled', $path);
    }

    /**
     * Run database migrations
     */
    protected function migrate(): static
    {
        $this->artisan('migrate');

        return $this;
    }

    /**
     * Get the user model class used in tests
     *
     * @return class-string<Model&Authenticatable>
     */
    protected function userModel(): string
    {
        return User::class;
    }

    /**
     * Set up a user
     */
    protected function setUpUser(): static
    {
        $this->user = $this->userModel()::factory()->create();

        $this->actingAs($this->user);

        return $this;
    }

    public function ignorePackageDiscoveriesFrom(): array
    {
        return [
            // A list of packages that should not be auto-discovered when running tests
            'laravel/telescope',
        ];
    }
}
