<?php

namespace OndrejVrto\Visitors\Database\Factories;

use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorsDataFactory extends Factory {
    protected $model = VisitorsData::class;

    public function definition() {
        $visitableModels = [
            "App\\Models\\Post",
            "App\\Models\\Page",
            "App\\Models\\Album",
            "App\\Models\\Article",
        ];

        $os = collect(OperatingSystem::cases())->pluck('value')->toArray();
        $category = collect(VisitorCategory::cases())->pluck('value')->toArray();

        return [
            'viewable_type'    => $this->faker->randomElement($visitableModels),
            'viewable_id'      => $this->faker->numberBetween(1, 50),
            'is_crawler'       => $this->faker->boolean(80),
            'category'         => $this->faker->randomElement($category),
            'country'          => $this->faker->countryCode(),
            'language'         => $this->faker->languageCode(),
            'operating_system' => $this->faker->randomElement($os),
            'visited_at'       => $this->faker->dateTimeBetween('-3 years', 'now'),
        ];
    }
}
