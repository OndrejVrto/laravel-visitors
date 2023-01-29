<?php

declare(strict_types=1);

test('return integer or zero', function ($valuesList, $expectedList): void {
    $resultList = intOrZero($valuesList);
    expect($resultList)->toBe($expectedList);
})->with(
    [
        'zero'              => [0, 0],
        'integer number'    => [123, 123],
        'binary number'     => [0b110101, 53],
        'hex number'        => [0x00000008, 8],
        'negative integer'  => [-123, -123],
        'integer in string' => ["123", 123],
        'decimal'           => [123.456, 123],
        'negative decimal'  => [-123.456, -123],
        'decimal in string' => ["123.456", 123],
        'null'              => [null, 0],
        'string'            => ['foo', 0],
        'object'            => [new stdClass(), 0],
        'array'             => [[5, 'foo'], 0],
        'boolean true'      => [true, 1],
        'boolean false'     => [false, 0],
    ]
);
