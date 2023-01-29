<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Data\StatisticsConfigData;
use OndrejVrto\Visitors\Data\ListPossibleQueriesData;
use OndrejVrto\Visitors\Services\ListPossibleQueries;
use OndrejVrto\Visitors\Tests\Support\Models\AnotherTestModel;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;

beforeEach(function() {
    $this->data = [
        [
            'viewable_type'    => TestModel::class,
            'viewable_id'      => 1,
            'category'         => VisitorCategory::WEB,
            'is_crawler'       => false,
            'language'         => '::lang::',
            'operating_system' => OperatingSystem::WINDOWS,
            'visited_at'       => now(),
        ],
        [
            'viewable_type'    => TestModel::class,
            'viewable_id'      => 2,
            'category'         => VisitorCategory::WEB,
            'is_crawler'       => true,
            'language'         => '::lang::',
            'operating_system' => OperatingSystem::WINDOWS,
            'visited_at'       => now()->addSecond(),
        ],
        [
            'viewable_type'    => AnotherTestModel::class,
            'viewable_id'      => 1,
            'category'         => VisitorCategory::API,
            'is_crawler'       => false,
            'language'         => '::lang::',
            'operating_system' => OperatingSystem::WINDOWS,
            'visited_at'       => now()->addSeconds(2),
        ],
        [
            'viewable_type'    => AnotherTestModel::class,
            'viewable_id'      => 2,
            'category'         => VisitorCategory::API,
            'is_crawler'       => true,
            'language'         => '::lang::',
            'operating_system' => OperatingSystem::WINDOWS,
            'visited_at'       => now()->addSeconds(3),
        ],
    ];

    VisitorsData::insert($this->data);
});

test('category and crawlers queries', function (
    bool      $generateCategory,
    bool      $generateCrawler,
    int       $expectCount,
    bool|null $expectCrawler,
    bool      $expectCrawlerLast,
    int|null  $expectCategoryLast,
): void {
    $configuration = new StatisticsConfigData(
        generateCategoryStatistics: $generateCategory,
        generateCrawlersStatistics: $generateCrawler,
        lastId                    : count($this->data),
        dataTableName             : (new VisitorsData())->getTable(),
        // irrelevant
        numberDaysStatistics      : 0,
        generateGraphs            : false,
        from                      : now(),
        to                        : now(),
    );

    $posibleQueries = (new ListPossibleQueries($configuration))->get();
    $resultCount = $posibleQueries->count();
    $first = $posibleQueries->first();
    $last = $posibleQueries->pop();

    expect($resultCount)->toBe($expectCount);

    expect($first)->toBeInstanceOf(ListPossibleQueriesData::class);
    expect($first->category)->toBeNull();
    expect($first->viewable_id)->toBeNull();
    expect($first->viewable_type)->toBeNull();
    expect($first->is_crawler)->toBe($expectCrawler);

    expect($last)->toBeInstanceOf(ListPossibleQueriesData::class);
    expect($last->viewable_type)->toBe(TestModel::class);
    expect($last->viewable_id)->toBe(2);
    expect($last->is_crawler)->toBe($expectCrawlerLast);
    expect($last->category)->toBe($expectCategoryLast);
})->with([
    "category and crawler is false" => [
        'generateCategory'   => false,
        'generateCrawler'    => false,
        'expectCount'        => 7,
        'expectCrawler'      => false,
        'expectCrawlerLast'  => false,
        'expectCategoryLast' => null,
    ],
    "category is true and crawler is false" => [
        'generateCategory'   => true,
        'generateCrawler'    => false,
        'expectCount'        => 15,
        'expectCrawler'      => false,
        'expectCrawlerLast'  => false,
        'expectCategoryLast' => 1,
    ],
    "category is false and crawler is true" => [
        'generateCategory'   => false,
        'generateCrawler'    => true,
        'expectCount'        => 17,
        'expectCrawler'      => null,
        'expectCrawlerLast'  => true,
        'expectCategoryLast' => null,
    ],
    "category and crawler is true" => [
        'generateCategory'   => true,
        'generateCrawler'    => true,
        'expectCount'        => 37,
        'expectCrawler'      => null,
        'expectCrawlerLast'  => true,
        'expectCategoryLast' => 1,
    ],
]);
