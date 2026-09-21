<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\Router;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionConnectivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'ip_address' => fake()->ipv4(),
            'pppoe_user' => fake()->userName(),
            'pppoe_secret' => fake()->password(8, 12),
        ];
    }
}
