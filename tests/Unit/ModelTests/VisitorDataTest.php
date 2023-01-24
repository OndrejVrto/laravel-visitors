<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Tests\TestCase;
use OndrejVrto\Visitors\Models\VisitorsData;

uses(TestCase::class);

it('can have a custom connection through config file', function (): void {
    config()->set('visitors.eloquent_connection', 'mysql');

    expect('mysql')->toBe((new VisitorsData())->getConnectionName());

    config()->set('visitors.eloquent_connection', 'mysql_visitors');

    expect('mysql_visitors')->toBe((new VisitorsData())->getConnectionName());
})->only();
