<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'user_id' => null,
            'client_code' => strtoupper($this->faker->bothify('??-?-#####')),
            'name' => $this->faker->company(),
            'type' => $this->faker->randomElement(array_keys(Client::TYPE_OPTIONS)),
            'address' => $this->faker->optional()->address(),
            'status' => 'active',
            'registered_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
