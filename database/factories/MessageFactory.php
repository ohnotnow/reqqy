<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'conversation_id' => Conversation::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->sentence,
        ];
    }

    public function assistant()
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
