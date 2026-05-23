<?php

namespace Database\Factories;

use App\Models\LearningLevel;
use App\Models\LearningTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LearningTopic>
 */
class LearningTopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'learning_level_id' => LearningLevel::factory(),
            'name' => Str::headline($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->sentence(),
            'position' => fake()->unique()->numberBetween(1, 1000),
            'is_active' => true,
        ];
    }
}
