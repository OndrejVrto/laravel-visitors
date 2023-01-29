<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Visit;
use OndrejVrto\Visitors\Traffic;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;

test('helper visit return visit object', function (): void {
    $model = TestModel::find(1);

    expect(visit($model))->toBeInstanceOf(Visit::class);
})->skip();

test('helper traffic return traffic object', function (): void {
    expect(traffic())->toBeInstanceOf(Traffic::class);
});
