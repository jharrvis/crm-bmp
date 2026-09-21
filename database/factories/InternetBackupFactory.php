<?php

namespace Database\Factories;

use App\Models\InternetBackup;
use App\Models\Subscription;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class InternetBackupFactory extends Factory
{
    protected $model = InternetBackup::class;

    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'subscription_id' => null,
            'name' => $this->faker->sentence(3),
            'circuit_id' => $this->faker->optional()->bothify('CID-#####'),
            'ip_address' => $this->faker->optional()->ipv4(),
            'gateway' => $this->faker->optional()->ipv4(),
            'bandwidth_mbps' => $this->faker->numberBetween(10, 1000),
            'monthly_cost' => $this->faker->optional()->randomFloat(2, 100000, 5000000),
            'active_date' => $this->faker->optional()->date(),
            'address' => $this->faker->optional()->address(),
            'status' => $this->faker->randomElement(['planned', 'active', 'suspended', 'terminated']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
