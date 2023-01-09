<?php

use OndrejVrto\Visitors\Action\CheckVisitable;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;
use OndrejVrto\Visitors\Tests\Support\Models\AnotherTestModel;
use OndrejVrto\Visitors\Tests\Support\Models\TestModelWithoutVisitableContract;

test('check visitable action', function ($visitable, $list) {
    $resultList = (new CheckVisitable())($visitable);
    expect($resultList)->toBe($list);
})->with(
    [
        'one good model' => [
            TestModel::class,
            [TestModel::class]
        ],
        'two good models' => [
            [TestModel::class, AnotherTestModel::class],
            [AnotherTestModel::class, TestModel::class]
        ],
        'one bad model' => [
            TestModelWithoutVisitableContract::class,
            []
        ],
        'good model from string' => [
            '\OndrejVrto\Visitors\Tests\Support\Models\TestModel',
            [TestModel::class]
        ],
        'two same models' => [
            [TestModel::class, '\OndrejVrto\Visitors\Tests\Support\Models\TestModel'],
            [TestModel::class]
        ],
        'multiple options' => [
            [
                5 => TestModel::class,
                100 => [TestModel::class, '\OndrejVrto\Visitors\Tests\Support\Models\TestModelWithoutVisitableContract'],
                'nested' => [
                    AnotherTestModel::class,
                    'level 2' => [
                        '\OndrejVrto\Visitors\Tests\Support\Models\TestModel'
                    ]
                ],
                TestModelWithoutVisitableContract::class,
            ],
            [0 => AnotherTestModel::class, 1 => TestModel::class]
        ],

    ]
    );
