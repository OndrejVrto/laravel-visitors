<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Spatie\LaravelPackageTools\Package;
use OndrejVrto\Visitors\Commands\VisitorsPruneCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use OndrejVrto\Visitors\Commands\VisitorsUpdateCommand;

class VisitorsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /**
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-visitors')
            ->hasConfigFile()
            ->hasMigrations([
                'create_all_visitors_tables',
            ])
            ->hasCommands([
                VisitorsPruneCommand::class,
                VisitorsUpdateCommand::class,
            ]);
    }
}
