<?php

use OndrejVrto\Visitors\Visit;
use OndrejVrto\Visitors\Traffic;
use OndrejVrto\Visitors\Statistics;

test('helper visit return visit object', function () {
    $visit = visit();

    expect($visit)->toBeObject()->and($visit)->toBeInstanceOf(Visit::class);
});

test('helper traffic return traffic object', function () {
    $traffic = traffic();

    expect($traffic)->toBeObject()->and($traffic)->toBeInstanceOf(Traffic::class);
});

test('helper statistics return statistics object', function () {
    $statistics = statistics();

    expect($statistics)->toBeObject()->and($statistics)->toBeInstanceOf(Statistics::class);
});
