<?php

namespace App\Application\Chat\Contracts;

use App\Models\ChatSession;

interface ConversationRepositoryContract
{
    /**
     * @return array{chat_session_id: int, conversation_id: string}
     */
    public function findOrCreateSession(?string $conversationId, ?int $userId, bool $isAuthenticated): array;

    public function findSessionByConversationId(string $conversationId): ?ChatSession;

    /**
     * @return array<int, array{role: string, content: string}>
     */
    public function getRecentMessages(int $chatSessionId, int $limit = 12): array;

    public function appendMessage(int $chatSessionId, string $role, string $content): void;

    public function persistExchange(int $chatSessionId, string $userMessage, string $assistantMessage): void;

    public function touchLastMessageAt(int $chatSessionId): void;

    public function countMessagesByConversation(string $conversationId): int;
}
