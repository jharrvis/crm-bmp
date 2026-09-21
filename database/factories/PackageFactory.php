<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = \App\Models\Package::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 100000, 1000000),
            'bandwidth_down' => $this->faker->numberBetween(10, 1000),
            'bandwidth_up' => $this->faker->numberBetween(10, 1000),
            'is_active' => true,
        ];
    }
}
