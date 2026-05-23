<?php

declare(strict_types=1);

namespace App\Application\Dialogflow\DTO;

readonly class DetectIntentInput
{
    public function __construct(
        public string $prompt,
        public ?string $conversationId = null,
    ) {}
}
