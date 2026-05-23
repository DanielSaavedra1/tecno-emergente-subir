<?php

namespace App\Http\Resources\Api\V1;

use App\Application\Chat\DTO\SendChatMessageOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SendChatMessageOutput
 */
class ChatMessageResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => 'success',
            'reply' => $this->reply,
            'conversation_id' => $this->conversationId,
            'memory_exchanges' => $this->memoryExchanges,
        ];
    }
}
