<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;

test('observer visitable model deleted', function (): void {
    $visitDataTableName = (new VisitorsData())->getTable();
    $visitExpireTableName = (new VisitorsExpires())->getTable();
    $testModel = TestModel::create(['name' => '::test_name::']);
    $visitData = [
        'viewable_type' => TestModel::class,
    ];

    visit($testModel)->increment();

    $this->assertModelExists($testModel);
    $this->assertDatabaseCount($visitDataTableName, 1);
    $this->assertDatabaseHas($visitDataTableName, $visitData);
    $this->assertDatabaseCount($visitExpireTableName, 1);
    $this->assertDatabaseHas($visitExpireTableName, $visitData);

    $testModel->delete();

    $this->assertModelMissing($testModel);
    $this->assertDatabaseCount($visitDataTableName, 0);
    $this->assertDatabaseMissing($visitDataTableName, $visitData);
    $this->assertDatabaseCount($visitExpireTableName, 0);
    $this->assertDatabaseMissing($visitExpireTableName, $visitData);
});
