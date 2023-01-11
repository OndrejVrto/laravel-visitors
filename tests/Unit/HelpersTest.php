<?php

use OndrejVrto\Visitors\Visit;
use OndrejVrto\Visitors\Traffic;
use OndrejVrto\Visitors\Statistics;

test('helper visit return visit object', function () {
    expect(visit())->toBeInstanceOf(Visit::class);
});

test('helper traffic return traffic object', function () {
    expect(traffic())->toBeInstanceOf(Traffic::class);
});

test('helper statistics return statistics object', function () {
    expect(statistics())->toBeInstanceOf(Statistics::class);
});
