<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Tests;

use PDO;
use Illuminate\Encryption\Encrypter;
use Orchestra\Testbench\TestCase as Orchestra;
use OndrejVrto\Visitors\VisitorsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;

class TestCase extends Orchestra {
    private const TEST_DATABASE = 'sqlite';

    /**
     * Setup the test environment.
     */
    protected function setUp(): void {
        // Code before Laravel application created.
        parent::setUp();
        // Code after Laravel application created.

        $this->setUpDatabase();

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
            'Traffic' => '\OndrejVrto\Visitors\Facades\Traffic'
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function getEnvironmentSetUp($app): void {
        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey(config()['app.cipher'])
        ));

        if (self::TEST_DATABASE === 'sqlite') {
            $app['config']->set('database.default', 'sqlite');
            $app['config']->set('database.connections.sqlite', [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ]);
        } else {
            $app['config']->set('database.default', 'mysql');
            $app['config']->set('database.connections.mysql', [
                'driver' => 'mysql',
                'url' => env('DATABASE_URL'),
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => 'laravel_visitors_test',
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
        }
    }

    protected function setUpDatabase(): void {
        $migration = include __DIR__.'/../database/migrations/create_all_visitors_tables.php.stub';
        $migration->up();

        $migrationTest = include __DIR__.'/Support/migrations/2023_01_12_000000_create_test_models_table.php';
        $migrationTest->up();

        TestModel::insert([
            ['name' => '::test name 1::'],
            ['name' => '::test name 2::'],
            ['name' => '::test name 3::'],
            ['name' => '::test name 4::'],
            ['name' => '::test name 5::'],
        ]);
    }

        /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown(): void {
        $migration = include __DIR__.'/../database/migrations/create_all_visitors_tables.php.stub';
        $migration->down();

        $migrationTest = include __DIR__.'/Support/migrations/2023_01_12_000000_create_test_models_table.php';
        $migrationTest->down();
    }
}
