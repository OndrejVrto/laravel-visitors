<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Spatie\LaravelPackageTools\Package;
use OndrejVrto\Visitors\Commands\VisitorsCleanCommand;
use OndrejVrto\Visitors\Commands\VisitorsFreshCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class VisitorsServiceProvider extends PackageServiceProvider {
    public function configurePackage(Package $package): void {
        $package
            ->name('laravel-visitors')
            ->hasConfigFile()
            ->hasMigration('create_all_visitors_tables')
            ->hasTranslations()
            ->hasCommands([
                VisitorsCleanCommand::class,
                VisitorsFreshCommand::class,
            ]);
    }

    public function packageRegistered(): void {
        $this->app->bind('visitor', fn (): Visitor => new Visitor());
        $this->app->alias(Visitor::class, 'visitor');

        $this->app->bind('traffic', fn (): Traffic => new Traffic());
        $this->app->alias(Traffic::class, 'traffic');

        $this->app->bind('statistics', fn (): Statistics => new Statistics());
        $this->app->alias(Statistics::class, 'statistics');
    }
}
