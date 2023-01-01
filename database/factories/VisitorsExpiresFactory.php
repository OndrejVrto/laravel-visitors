<?php

namespace OndrejVrto\Visitors\Database\Factories;

use OndrejVrto\Visitors\Enums\VisitorCategory;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorsExpiresFactory extends Factory {
    protected $model = VisitorsExpires::class;

    public function definition() {
        $visitableModels = [
            "App\\Models\\Post",
            "App\\Models\\Page",
            "App\\Models\\Album",
            "App\\Models\\Article",
        ];

        $category = collect(VisitorCategory::cases())->pluck('value')->toArray();

        return [
            'viewable_type'    => $this->faker->randomElement($visitableModels),
            'viewable_id'      => $this->faker->numberBetween(1, 50),
            'category'         => $this->faker->randomElement($category),
            'ip_address'       => $this->faker->boolean(30) ? $this->faker->ipv4() : $this->faker->ipv6(),
            'expires_at'       => $this->faker->dateTimeBetween('-3 hours', '+2 hours'),
        ];
    }
}
