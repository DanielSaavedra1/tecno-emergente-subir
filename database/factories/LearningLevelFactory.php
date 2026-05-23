<?php

namespace Database\Factories;

use App\Models\LearningLevel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LearningLevel>
 */
class LearningLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'name' => Str::headline($title),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'title' => Str::headline($title),
            'description' => fake()->sentence(),
            'position' => fake()->unique()->numberBetween(1, 1000),
            'is_active' => true,
        ];
    }
}
