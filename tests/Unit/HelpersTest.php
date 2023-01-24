<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Visit;
use OndrejVrto\Visitors\Traffic;
use OndrejVrto\Visitors\Statistics;

test('helper visit return visit object', function (): void {
    expect(visit())->toBeInstanceOf(Visit::class);
});

test('helper traffic return traffic object', function (): void {
    expect(traffic())->toBeInstanceOf(Traffic::class);
});

test('helper statistics return statistics object', function (): void {
    expect(statistics())->toBeInstanceOf(Statistics::class);
});
