<?php

namespace App\Infrastructure\Persistence;

use App\Application\Chat\Contracts\ConversationRepositoryContract;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EloquentConversationRepository implements ConversationRepositoryContract
{
    public function findOrCreateSession(?string $conversationId, ?int $userId, bool $isAuthenticated): array
    {
        if ($conversationId !== null) {
            $session = $this->findSessionByConversationId($conversationId);

            if ($session !== null) {
                return [
                    'chat_session_id' => $session->id,
                    'conversation_id' => $session->conversation_id,
                ];
            }
        }

        $session = ChatSession::query()->create([
            'conversation_id' => $conversationId ?? $this->newConversationId(),
            'user_id' => $isAuthenticated ? $userId : null,
            'last_message_at' => Carbon::now(),
        ]);

        return [
            'chat_session_id' => $session->id,
            'conversation_id' => $session->conversation_id,
        ];
    }

    public function findSessionByConversationId(string $conversationId): ?ChatSession
    {
        return ChatSession::query()
            ->where('conversation_id', $conversationId)
            ->first();
    }

    public function getRecentMessages(int $chatSessionId, int $limit = 12): array
    {
        // 1. Buscamos por id de forma descendente para asegurar el orden secuencial de inserción físico
        $messages = ChatMessage::query()
            ->where('chat_session_id', $chatSessionId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['role', 'content'])
            ->reverse() // Al invertir el orden descendente del id, queda perfectamente cronológico
            ->map(fn (ChatMessage $message): array => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->all();

        // 2. Retornamos las llaves limpias e indexadas consecutivamente
        return array_values($messages);
    }

    public function appendMessage(int $chatSessionId, string $role, string $content): void
    {
        ChatMessage::query()->create([
            'chat_session_id' => $chatSessionId,
            'role' => $role,
            'content' => $content,
            'created_at' => Carbon::now(),
        ]);
    }

    public function persistExchange(int $chatSessionId, string $userMessage, string $assistantMessage): void
    {
        DB::transaction(function () use ($chatSessionId, $userMessage, $assistantMessage): void {
            $this->appendMessage($chatSessionId, 'user', $userMessage);
            $this->appendMessage($chatSessionId, 'assistant', $assistantMessage);
            $this->touchLastMessageAt($chatSessionId);
        });
    }

    public function touchLastMessageAt(int $chatSessionId): void
    {
        ChatSession::query()->whereKey($chatSessionId)->update([
            'last_message_at' => Carbon::now(),
        ]);
    }

    public function countMessagesByConversation(string $conversationId): int
    {
        return ChatMessage::query()
            ->whereHas('session', fn ($query) => $query->where('conversation_id', $conversationId))
            ->count();
    }

    private function newConversationId(): string
    {
        do {
            $conversationId = bin2hex(random_bytes(32));
        } while (ChatSession::query()->where('conversation_id', $conversationId)->exists());

        return $conversationId;
    }
}
