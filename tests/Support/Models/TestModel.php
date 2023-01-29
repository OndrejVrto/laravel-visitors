<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Traits\InteractsWithVisits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OndrejVrto\Visitors\Tests\Support\Factories\TestModelFactory;

class TestModel extends Model implements Visitable {
    use HasFactory;
    use InteractsWithVisits;

    public $guarded = [];

    protected $table = 'test_models';

    protected static function newFactory(): Factory {
        return new TestModelFactory();
    }
}
