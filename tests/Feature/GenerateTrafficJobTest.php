<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use OndrejVrto\Visitors\Jobs\GenerateTrafficJob;
use OndrejVrto\Visitors\Services\TrafficGenerator;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;

test('traffic job is dispached', function () {
    $testModel = TestModel::factory()->create();
    visit($testModel)->forceIncrement();

    Bus::fake();

    (new TrafficGenerator())->run();

    Bus::assertDispatched(GenerateTrafficJob::class);
});

