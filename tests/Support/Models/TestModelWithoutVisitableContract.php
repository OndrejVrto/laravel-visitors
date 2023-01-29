<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OndrejVrto\Visitors\Tests\Support\Factories\TestModelFactory;

class TestModelWithoutVisitableContract extends Model {
    use HasFactory;

    public $guarded = [];

    protected $table = 'test_models';

    protected static function newFactory(): Factory {
        return new TestModelFactory();
    }
}
