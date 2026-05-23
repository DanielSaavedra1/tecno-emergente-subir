<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\LearningTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'learning_topic_id' => LearningTopic::factory(),
            'number' => fake()->unique()->numerify('##'),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'exercise_type' => 'function',
            'function_name' => 'solve',
            'input_description' => 'La función recibirá los valores como parámetros.',
            'output_description' => 'La función debe retornar el resultado esperado.',
            'examples' => [
                ['arguments' => [1, 2], 'expected' => 3],
            ],
            'considerations' => [
                'No uses input() dentro de la función.',
            ],
            'starter_code' => "def solve(a, b):\n    pass\n",
            'test_cases' => [
                ['arguments' => [1, 2], 'expected' => 3, 'is_public' => true],
            ],
            'difficulty' => fake()->randomElement(['basic', 'intermediate', 'advanced']),
            'position' => fake()->unique()->numberBetween(1, 1000),
            'is_active' => true,
        ];
    }
}
