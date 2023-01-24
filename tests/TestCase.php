<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Tests;

use PDO;
use Closure;
use Mockery;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use OndrejVrto\Visitors\VisitorsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Factory;

use function Orchestra\Testbench\artisan;

class TestCase extends Orchestra {
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void {
        // Code before Laravel application created.
        parent::setUp();
        // Code after Laravel application created.

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'OndrejVrto\\Visitors\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array {
        return [
            VisitorsServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<string,class-string>
     */
    protected function getPackageAliases($app): array {
        return [
            'Visit' => '\OndrejVrto\Visitors\Facades\Visit',
            'Traffic' => '\OndrejVrto\Visitors\Facades\Traffic',
            'Statistics' => '\OndrejVrto\Visitors\Facades\Statistics',
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function getEnvironmentSetUp($app): void {
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => 'test_visitors_laravel',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            // 'collation' => 'utf8mb4_unicode_ci',
            'collation' => 'utf8mb4_slovak_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);

        $app['config']->set('database.connections.mysql_visitors', [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => 'test_visitors_package',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            // 'collation' => 'utf8mb4_unicode_ci',
            'collation' => 'utf8mb4_slovak_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('visitors.eloquent_connection', 'mysql_visitors');

        // dd($app['config']);
    }

    /**
     * Define database migrations.
     */
    protected function defineDatabaseMigrations(): void {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/Support/migrations');
    }

    /**
     * Perform any work that should take place before the database has started refreshing.
     */
    protected function beforeRefreshingDatabase(): void {
        $migration = include __DIR__.'/../database/migrations/create_all_visitors_tables.php.stub';
        $migration->down();
        // $this->publishPackageMigrations();
    }

    /**
     * Perform any work that should take place once the database has finished refreshing.
     */
    protected function afterRefreshingDatabase(): void {
        // $this->destroyPackageMigrations();
        $migration = include __DIR__.'/../database/migrations/create_all_visitors_tables.php.stub';
        $migration->up();
    }

    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void {
        $migration = include __DIR__.'/../database/migrations/create_all_visitors_tables.php.stub';
        $migration->down();

        // Mockery::close();
        // Carbon::setTestNow();
    }

    /**
     * Publish package migrations.
     */
    // protected function publishPackageMigrations(): void {
    //     $this->artisan('vendor:publish', [
    //         '--force' => '',
    //         '--tag' => 'visitors-migrations',
    //     ]);
    // }

    /**
     * Delete all published migrations.
     */
    // protected function destroyPackageMigrations(): void {
    //     File::cleanDirectory('./vendor/orchestra/testbench-core/laravel/database/migrations');
    // }

    /**
     * Perform package database migrations.
     */
    // protected function migratePackageTables(): void {
    //     $this->loadMigrationsFrom([
    //         '--realpath' => true,
    //     ]);
    // }

    /**
     * Perform unit test database migrations.
     */
    // protected function migrateUnitTestTables(): void {
    //     $this->loadMigrationsFrom(
    //         __DIR__.'/database/migrations'
    //         // __DIR__.'/tests/Support/database/migrations'
    //     );
    // }

    /**
     * Register package related model factories.
     */
    // protected function registerPackageFactories(): void {
    //     $this
    //         ->withFactories(realpath(__DIR__.'/Support/database/factories'));
    //         // ->withFactories(realpath(__DIR__.'/database/factories'));
    // }

    /**
     * Mock an instance of an object in the container.
     *
     * @param  string  $abstract
     * @param  Closure|null  $mock
     * @return object
     */
    // protected function mock($abstract, Closure $mock = null): object {
    //     return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    // }
}
