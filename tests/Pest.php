<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/
uses(\OndrejVrto\Visitors\Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function insertTestData() {
    $data = [
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

    VisitorsData::insert($data);
}
