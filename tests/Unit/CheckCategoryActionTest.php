<?php

use OndrejVrto\Visitors\Action\CheckCategory;
use OndrejVrto\Visitors\Enums\VisitorCategory;

test('check category action', function ($category, $expectedList) {
    $resultList = (new CheckCategory())($category);
    expect($resultList)->toBe($expectedList);
})->with(
    [
        'one good category' => [
            VisitorCategory::API,
            [2]
        ],
        'multiple good category in array' => [
            [VisitorCategory::API, VisitorCategory::WEB],
            [1, 2]
        ],
        'good number' =>[
            1,
            [1]
        ],
        'good numbers' =>[
            [2, 1, 110, 105],
            [1, 2, 105, 110]
        ],
        'bad number' =>[
            12345,
            []
        ],
        'bad numbers' =>[
            [12345, 200, 185],
            []
        ],
        'good string' => [
            'GUEST',
            [4]
        ],
        'good strings' => [
            ['MANUAL', 'custom_10'],
            [5, 110]
        ],
        'bad string' => [
            'nonExistsCategoryName',
            []
        ],
        'multiple same category' => [
            [VisitorCategory::WEB, VisitorCategory::WEB, 'web', 1],
            [1]
        ],
        'nested array' => [
            [['api', ['guest', ['api']]], 'foo' => [5 => 'web', 'bar' => VisitorCategory::UNDEFINED]],
            [0, 1, 2, 4]
        ],
        'multiple options' => [
            [
                'UNDEFINED', 'web', 'web', 'web', 'nonExistsCategoryName',  //strings
                'a' => ['CUSTOM_02', VisitorCategory::CUSTOM_05, 1],  // nested array
                VisitorCategory::CUSTOM_01, // enum
                5, 12345 // numbers
            ],
            [0, 1, 5, 101, 102, 105]
        ]
    ]
);
