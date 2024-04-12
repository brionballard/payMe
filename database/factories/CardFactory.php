<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Database\Factories\FactoryHelper;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    use FactoryHelper;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = $this->getRandomUser([]);

        return [
            'user_id' => $user->id,
            'number' => fake()->creditCardNumber(),
            'name' => $user->name,
            'exp' => fake()->creditCardExpirationDateString(),
            'cvc' => '000'
        ];
    }
}
