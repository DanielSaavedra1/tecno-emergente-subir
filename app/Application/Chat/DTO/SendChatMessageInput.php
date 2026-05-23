<?php

namespace App\Application\Chat\DTO;

class SendChatMessageInput
{
    public function __construct(
        public string $prompt,
        public ?string $conversationId,
        public ?int $userId,
        public bool $isAuthenticated,
        public int $exerciseId,
        public ?string $sourceCode = null,
        public ?string $output = null,
    ) {}
}
