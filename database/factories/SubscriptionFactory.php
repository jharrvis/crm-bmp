<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Package;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'package_id' => Package::factory(),
            'subscription_code' => strtoupper($this->faker->bothify('??-?-#####')),
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended', 'terminated']),
            'installed_at' => $this->faker->optional()->dateTime(),
            'billing_cycle_day' => $this->faker->numberBetween(1, 28),
            'next_billing_date' => $this->faker->optional()->date(),
            'price_at_subscription' => $this->faker->randomFloat(2, 100000, 1000000),
        ];
    }
}
