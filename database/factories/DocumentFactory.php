<?php

namespace Database\Factories;

use App\DocumentType;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'type' => DocumentType::Prd,
            'name' => $this->faker->sentence(3),
            'content' => $this->faker->paragraphs(nb: rand(3, 30), asText: true),
            'metadata' => null,
        ];
    }

    public function prd(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::Prd,
            'name' => 'Product Requirement Document',
            'metadata' => null,
        ]);
    }

    public function technicalAssessment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::TechnicalAssessment,
            'name' => 'Technical Assessment',
            'metadata' => [
                'model' => 'anthropic/claude-3-5-sonnet-20241022',
                'prompt_version' => 'v1.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function research(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DocumentType::Research,
            'name' => 'Existing Solution Research',
            'metadata' => null,
        ]);
    }
}
