<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Traits\InteractsWithVisits;

class TestModel extends Model implements Visitable {
    use InteractsWithVisits;

    public $guarded = [];

    protected $table = 'test_models';
}
