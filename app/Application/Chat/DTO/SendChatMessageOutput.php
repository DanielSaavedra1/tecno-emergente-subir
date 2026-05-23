<?php

namespace App\Application\Chat\DTO;

class SendChatMessageOutput
{
    public function __construct(
        public string $reply,
        public string $conversationId,
        public int $memoryExchanges,
    ) {}
}
