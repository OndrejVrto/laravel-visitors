<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Spatie\LaravelPackageTools\Package;
use OndrejVrto\Visitors\Commands\VisitorsCleanCommand;
use OndrejVrto\Visitors\Commands\VisitorsFreshCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class VisitorsServiceProvider extends PackageServiceProvider {
    public function configurePackage(Package $package): void {
        /**
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-visitors')
            ->hasConfigFile()
            ->hasMigration('create_all_visitors_tables')
            ->hasTranslations()
            ->hasCommands([
                VisitorsFreshCommand::class,
                VisitorsCleanCommand::class,
            ]);
    }
}
