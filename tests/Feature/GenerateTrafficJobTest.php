<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Data\GraphProperties;
use OndrejVrto\Visitors\Models\VisitorsTraffic;
use OndrejVrto\Visitors\Jobs\GenerateTrafficJob;
use OndrejVrto\Visitors\Data\StatisticsConfigData;
use OndrejVrto\Visitors\Services\TrafficGenerator;
use OndrejVrto\Visitors\Data\ListPossibleQueriesData;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;

beforeEach(function (): void {
    insertTestData();

    $this->dataTableName = (new VisitorsData())->getTable();
    $this->lastID = VisitorsData::selectRaw("max(`data_id`) as `last_id`")->first()->last_id;

    $this->listPossibleQueries = new Collection([
        new ListPossibleQueriesData(
            viewable_type: null,
            viewable_id  : null,
            is_crawler   : null,
            category     : null,
        ),
        new ListPossibleQueriesData(
            viewable_type: TestModel::class,
            viewable_id  : 1,
            is_crawler   : false,
            category     : 1,
        ),
        new ListPossibleQueriesData(
            viewable_type: TestModel::class,
            viewable_id  : 2,
            is_crawler   : true,
            category     : 1,
        ),
    ]);

    $this->graphProperties = new GraphProperties();
});

test('traffic job is dispached', function (): void {
    $testModel = TestModel::first();
    visit($testModel)->forceIncrement();

    Bus::fake();

    (new TrafficGenerator())->run();

    Bus::assertDispatched(GenerateTrafficJob::class);
});


test('todo', function (): void {
    $configuration = new StatisticsConfigData(
        generateCategoryStatistics: true,
        generateCrawlersStatistics: true,
        generateGraphs            : true,
        lastId                    : $this->lastID,
        numberDaysStatistics      : 100,
        from                      : now(),
        to                        : now(),
        dataTableName             : $this->dataTableName,
    );

    (new GenerateTrafficJob($this->listPossibleQueries, $this->graphProperties, $configuration))->handle();

    expect(3)->toBe(VisitorsTraffic::count());

    // dump(VisitorsTraffic::get());
});
