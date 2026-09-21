<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'cid' => $this->faker->optional()->bothify('CID-#####'),
            'address' => $this->faker->optional()->address(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
