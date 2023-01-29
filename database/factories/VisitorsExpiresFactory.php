<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Database\Factories;

use DateTime;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorsExpiresFactory extends Factory {
    protected $model = VisitorsExpires::class;

    /**
     * @return array{viewable_type:string,viewable_id:int,category:int,ip_address:string,expires_at:DateTime}
     */
    public function definition() {
        $visitableModels = [
            TestModel::class,
            AnotherTestModel::class
        ];

        $category = collect(VisitorCategory::cases())->pluck('value')->toArray();

        return [
            'viewable_type' => $this->faker->randomElement($visitableModels),
            'viewable_id'   => $this->faker->numberBetween(1, 10),
            'category'      => $this->faker->randomElement($category),
            'ip_address'    => $this->faker->boolean(30) ? $this->faker->ipv4() : $this->faker->ipv6(),
            'expires_at'    => $this->faker->dateTimeBetween('-3 hours', '+1 hours'),
        ];
    }
}
