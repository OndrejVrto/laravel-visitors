<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use OndrejVrto\Visitors\Tests\Support\Models\TestModel;

class TestModelFactory extends Factory {
    protected $model = TestModel::class;

    /** @return array<string,string> */
    public function definition() {
        return [
            'name' => '::name::'
        ];
    }
}
