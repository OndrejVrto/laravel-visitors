<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Database\Factories;

use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Enums\OperatingSystem;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorsDataFactory extends Factory {
    protected $model = VisitorsData::class;

    /**
     * @return array{viewable_type:string,viewable_id:int,is_crawler:bool,category:int,country:string,language:string,operating_system:int,visited_at:\DateTime}
     */
    public function definition() {
        $visitableModels = [
            "App\\Models\\News",
            "App\\Models\\Faq",
            "App\\Models\\StaticPage",
        ];

        // $os = collect(OperatingSystem::cases())->pluck('value')->toArray();
        // $category = collect(VisitorCategory::cases())->pluck('value')->toArray();

        return [
            'viewable_type'    => $this->faker->randomElement($visitableModels),
            'viewable_id'      => $this->faker->numberBetween(1, 10),
            'is_crawler'       => $this->faker->boolean(50),
            // 'category'         => $this->faker->randomElement($category),
            'category'         => $this->faker->numberBetween(1, 3),
            'country'          => $this->faker->countryCode(),
            'language'         => $this->faker->languageCode(),
            // 'operating_system' => $this->faker->randomElement($os),
            'operating_system' => $this->faker->numberBetween(1, 9),
            'visited_at'       => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
