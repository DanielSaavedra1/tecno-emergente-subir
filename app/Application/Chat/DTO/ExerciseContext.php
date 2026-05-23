<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

readonly class ExerciseContext
{
    /**
     * @param  string  $title  Exercise title
     * @param  string  $description  Exercise description
     * @param  string|null  $functionName  Expected function name
     * @param  string|null  $inputDescription  What the function receives
     * @param  string|null  $outputDescription  What the function should return
     * @param  array<string, mixed>|null  $examples  Public examples (already redacted)
     * @param  array<string, mixed>|null  $considerations  Hints and constraints
     * @param  string|null  $sourceCode  User's current source code
     * @param  string|null  $output  Visible output or error from last run
     */
    public function __construct(
        public string $title,
        public string $description,
        public ?string $functionName,
        public ?string $inputDescription,
        public ?string $outputDescription,
        public ?array $examples,
        public ?array $considerations,
        public ?string $sourceCode,
        public ?string $output,
    ) {}
}
