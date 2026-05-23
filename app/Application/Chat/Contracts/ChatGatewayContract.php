<?php

namespace App\Application\Chat\Contracts;

interface ChatGatewayContract
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function generateReply(array $messages): string;
}
