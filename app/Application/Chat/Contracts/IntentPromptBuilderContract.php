<?php

declare(strict_types=1);

namespace App\Application\Chat\Contracts;

use App\Application\Chat\DTO\ExerciseContext;
use App\Application\Dialogflow\DTO\DetectedIntent;

interface IntentPromptBuilderContract
{
    /**
     * Build the prompt that will be sent to LM Studio based on the detected intent,
     * the original user message, and the exercise context.
     *
     * @param  string  $userMessage  The original text the user typed
     * @param  DetectedIntent  $intent  The intent detected by Dialogflow
     * @param  ExerciseContext  $exercise  The current exercise context (no hidden tests)
     * @return string The prompt to send to LM Studio
     */
    public function build(string $userMessage, DetectedIntent $intent, ExerciseContext $exercise): string;
}
