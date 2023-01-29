<?php

declare(strict_types=1);

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
            "App\\Models\\Album",
            "App\\Models\\Article",
        ];

        $os = collect(OperatingSystem::cases())->pluck('value')->toArray();
        $category = collect(VisitorCategory::cases())->pluck('value')->pop(3)->toArray();

        return [
            'viewable_type'    => $this->faker->randomElement($visitableModels),
            'viewable_id'      => $this->faker->numberBetween(1, 5),
            'is_crawler'       => $this->faker->boolean(50),
            'category'         => $this->faker->randomElement($category),
            'language'         => $this->faker->languageCode(),
            'operating_system' => $this->faker->randomElement($os),
            'visited_at'       => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
