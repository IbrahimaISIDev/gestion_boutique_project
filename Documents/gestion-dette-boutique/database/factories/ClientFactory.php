<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'adresse'=>$this->faker->address(),
            'telephone' => $this->faker->numerify('77#######'),
            'surname' => $this->faker->userName(),

        ];
    }

    public function withUser(): static
    {
        return $this->afterMaking(function (Client $client) {
            if (!$client->user_id) {
                $user = User::factory()->client()->create();
                $client->user_id = $user->id;
            }
        });
    }
}
