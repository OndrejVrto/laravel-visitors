<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Spatie\LaravelPackageTools\Package;
use Illuminate\Console\Scheduling\Schedule;
use OndrejVrto\Visitors\Traits\VisitorsSettings;
use OndrejVrto\Visitors\Commands\VisitorsCleanCommand;
use OndrejVrto\Visitors\Commands\VisitorsFreshCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class VisitorsServiceProvider extends PackageServiceProvider {
    use VisitorsSettings;

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
        $this->app->bind('visit', fn (): Visit => new Visit());
        $this->app->alias(Visit::class, 'visit');

        $this->app->bind('traffic', fn (): Traffic => new Traffic());
        $this->app->alias(Traffic::class, 'traffic');

        $this->app->bind('statistics', fn (): Statistics => new Statistics());
        $this->app->alias(Statistics::class, 'statistics');
    }

    public function packageBooted(): void {
        if ($this->scheduleGenerateTrafficData()) {
            $this->app->booted(function (): void {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command(VisitorsCleanCommand::class)->weekly();
                $schedule->command(VisitorsFreshCommand::class)->everyThreeHours();
            });
        }
    }
}
