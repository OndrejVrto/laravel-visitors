<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;

class TestModelWithoutVisitableContract extends Model {
    public $guarded = [];

    protected $table = 'test_models';
}
