<?php

declare(strict_types=1);

use OndrejVrto\Visitors\Tests\Support\Models\TestModel;
use OndrejVrto\Visitors\Utilities\CartesianCombinations;

test('check cartesian combinations', function ($valuesList, $expectedList): void {
    $resultList = (new CartesianCombinations())->forItem($valuesList)->get();
    expect($resultList)->toBeArray()->and($resultList)->toBe($expectedList);
})->with(
    [
        'null' => [
            null,
            [[]]
        ],
        'null value' => [
            [1 => null],
            [[]]
        ],
        'empty' => [
            [],
            [[]]
        ],
        'one value' => [
            ['foo'],
            [['foo']]
        ],
        'only values' => [
            ['foo', 'bar', 1, TestModel::class],
            [['foo', 'bar', 1, TestModel::class]]
        ],
        'one array' => [
            [['foo', 'bar', 1, TestModel::class]],
            [['foo', 'bar', 1, TestModel::class]]
        ],
        'array and value' => [
            [['foo'], 'bar', 1, TestModel::class],
            [['foo', 'bar', 1, TestModel::class]]
        ],
        'two array with one element' => [
            [['foo'], ['bar']],
            [['foo', 'bar']]
        ],
        'two array combine three elements' => [
            [['foo1', 'foo2'], ['bar1']],
            [['foo1', 'bar1'], ['foo2', 'bar1']]
        ],
        'two array combine four elements' => [
            [['foo1', 'foo2'], ['bar1', 'bar2']],
            [['foo1', 'bar1'], ['foo1', 'bar2'], ['foo2', 'bar1'], ['foo2', 'bar2']]
        ],
        'three array combine six elements' => [
            [['foo1', 'foo2'], ['bar1', 'bar2'], [TestModel::class, 1000]],
            [
                ['foo1', 'bar1', TestModel::class], ['foo1', 'bar1', 1000],
                ['foo1', 'bar2', TestModel::class], ['foo1', 'bar2', 1000],
                ['foo2', 'bar1', TestModel::class], ['foo2', 'bar1', 1000],
                ['foo2', 'bar2', TestModel::class], ['foo2', 'bar2', 1000],
            ]
        ],
        'three nested array combine six elements with keys' => [
            [
                [100 => 'foo1', 5 => ['foo2']],
                [0 => ["X" => 'bar1'], 8 => ["Y" => 'bar2']],
                [TestModel::class, 'A' => 1000]
            ],
            [
                0 => [0 => 'foo1', 1 => 'bar1', 2 => TestModel::class], 1 => [0 => 'foo1', 1 => 'bar1', 2 => 1000],
                2 => [0 => 'foo1', 1 => 'bar2', 2 => TestModel::class], 3 => [0 => 'foo1', 1 => 'bar2', 2 => 1000],
                4 => [0 => 'foo2', 1 => 'bar1', 2 => TestModel::class], 5 => [0 => 'foo2', 1 => 'bar1', 2 => 1000],
                6 => [0 => 'foo2', 1 => 'bar2', 2 => TestModel::class], 7 => [0 => 'foo2', 1 => 'bar2', 2 => 1000],
            ]
        ],
    ]
);

test('check cartesian combinations with object', function (): void {
    $object = new TestModel();
    $input = [['foo1', 'foo2'], [$object]];
    $output = [['foo1', $object], ['foo2', $object]];

    $result = (new CartesianCombinations())->forItem($input)->get();

    expect($result)->toBeArray()->and($result)->toBe($output);

    $result = (new CartesianCombinations())
        ->forItem([$object])
        ->addItemWhen(true, ['foo'], ['xxx'])
        ->addItemWhen(false, ['xxx'], ['bar'])
        ->get();
    $output = [[$object, 'foo', 'bar']];

    expect($result)->toBeArray()->and($result)->toBe($output);
});
