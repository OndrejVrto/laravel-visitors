<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Tests;

use Closure;
use Mockery;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use OndrejVrto\Visitors\VisitorsServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestCase extends Orchestra {
    // use RefreshDatabase;

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

        // $this->destroyPackageMigrations();
        // $this->publishPackageMigrations();
        // $this->migratePackageTables();
        // $this->migrateUnitTestTables();
        // $this->registerPackageFactories();
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
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        // TODO: migrate visitors table to database connection from config file
        // $migration = include __DIR__.'/../database/migrations/create_all_visitors_tables.php.stub';
        // $migration->up();
    }





    /**
     * Clean up the testing environment before the next test.
     */
    // protected function tearDown(): void {
    //     Mockery::close();
    //     Carbon::setTestNow();
    // }

    /**
     * Publish package migrations.
     */
    // protected function publishPackageMigrations(): void {
    //     $this->artisan('vendor:publish', [
    //         '--force' => '',
    //         '--tag' => 'migrations',
    //     ]);
    // }

    /**
     * Delete all published migrations.
     */
    // protected function destroyPackageMigrations(): void {
    //     File::cleanDirectory('vendor/orchestra/testbench-core/laravel/database/migrations');
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
     * @param  \Closure|null  $mock
     * @return object
     */
    // protected function mock($abstract, Closure $mock = null): object {
    //     return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    // }
}
