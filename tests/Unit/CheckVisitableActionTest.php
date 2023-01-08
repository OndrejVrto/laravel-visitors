<?php

use OndrejVrto\Visitors\Action\CheckVisitable;
use OndrejVrto\Visitors\Enums\VisitorCategory;

test('check visitable action', function ($visitable, $list) {
    $resultList = (new CheckVisitable())($visitable);
    expect($resultList)->toBe($list);
})->with(
    [
        'one good model' => [
            StaticPage::class,
            [2]
        ],
    ]
)->skip();
