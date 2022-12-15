<?php

namespace OndrejVrto\Visitors\Database\Factories;

use Illuminate\Support\Str;
use OndrejVrto\Visitors\Models\VisitorsExpires;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorsExpiresFactory extends Factory {
    protected $model = VisitorsExpires::class;

    public function definition() {
        // $name = Str::upper(("SHOP-" . $this->faker->citySuffix() . "-" . $this->faker->word() . $this->faker->randomDigit()));
        // $enabled = (bool) $this->faker->boolean(70);

        return [
            // "uuid"         => $this->faker->uuid(),
            // "enabled"      => $enabled,
            // "name"         => $name,
            // "slug"         => Str::slug($name),
            // "preety_name"  => $this->faker->city() . " " . $this->faker->word(),
            // "server_type"  => $this->faker->randomElement(CoreServerType::cases()),
            // "ip"           => $this->faker->ipv4(),
            // "api_key"      => $this->faker->numerify("XX-000000000000##########"),
            // "api_password" => $this->faker->password(),
            // "description"  => $enabled ? null : $this->faker->sentence(3),
        ];
    }
}
