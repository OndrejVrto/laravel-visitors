<?php

namespace OndrejVrto\Visitors\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use OndrejVrto\Visitors\VisitorsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestCase extends Orchestra {

    protected function setUp(): void {

        // Code before Laravel application created.
        parent::setUp();
        // Code after Laravel application created.

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'OndrejVrto\\Visitors\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app) {
        return [
            VisitorsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app) {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
