<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Chat\DTO\SendChatMessageInput;
use App\Application\Chat\UseCases\SendChatMessageUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreChatMessageRequest;
use App\Http\Resources\Api\V1\ChatMessageResource;
use Illuminate\Http\JsonResponse;

class ChatMessageController extends Controller
{
    public function store(StoreChatMessageRequest $request, SendChatMessageUseCase $useCase): JsonResponse
    {
        $output = $useCase->handle(new SendChatMessageInput(
            prompt: $request->string('prompt')->toString(),
            conversationId: $request->input('conversation_id'),
            userId: $request->user()?->id,
            isAuthenticated: $request->user() !== null,
            exerciseId: $request->integer('exercise_id'),
            sourceCode: $request->input('source_code'),
            output: $request->input('output'),
        ));

        return (new ChatMessageResource($output))->response();
    }
}
