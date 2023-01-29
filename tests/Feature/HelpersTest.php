<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Visit;
use OndrejVrto\Visitors\Traffic;
use OndrejVrto\Visitors\Enums\StatusVisit;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;

test('helper visit return visit object', function (): void {
    $model = TestModel::find(3);
    $visit = visit($model);
    $status = $visit->increment();

    expect($visit)->toBeInstanceOf(Visit::class);
    expect($status)->toBeInstanceOf(StatusVisit::class);
});

test('helper traffic return traffic object', function (): void {
    expect(traffic())->toBeInstanceOf(Traffic::class);
});
