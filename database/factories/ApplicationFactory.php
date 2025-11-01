<?php

namespace Database\Factories;

use App\ApplicationCategory;
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
            'category' => ApplicationCategory::Internal,
            'name' => $this->faker->word,
            'short_description' => $this->faker->sentence,
            'overview' => $this->faker->sentence,
            'is_automated' => false,
            'status' => $this->faker->randomElement(['plan', 'approved', 'rejected']),
            'url' => $this->faker->url,
            'repo' => $this->faker->url,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ApplicationCategory::Internal,
        ]);
    }

    public function external(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ApplicationCategory::External,
            'repo' => null,
            'is_automated' => false,
        ]);
    }

    public function proposed(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => ApplicationCategory::Proposed,
            'url' => null,
            'repo' => null,
            'is_automated' => false,
            'status' => null,
        ]);
    }

    public function automated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_automated' => true,
        ]);
    }
}
