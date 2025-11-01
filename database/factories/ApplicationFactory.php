<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'short_description' => $this->faker->sentence,
            'overview' => $this->faker->sentence,
            'is_automated' => false,
            'status' => $this->faker->randomElement(['plan', 'approved', 'rejected']),
            'url' => $this->faker->url,
            'repo' => $this->faker->url,
        ];
    }

    public function automated()
    {
        return $this->state(fn (array $attributes) => [
            'is_automated' => true,
        ]);
    }
}
