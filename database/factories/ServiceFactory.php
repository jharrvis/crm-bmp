<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = \App\Models\Service::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'code' => strtoupper($this->faker->bothify('??-??')),
            'type' => $this->faker->randomElement(['internet', 'mail', 'hosting', 'domain']),
            'is_active' => true,
        ];
    }
}
