<?php

namespace App\Http\Requests\Api\V1;

use App\Application\Chat\Contracts\ConversationRepositoryContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $conversationId = $this->input('conversation_id');

                if ($conversationId === null || $conversationId === '') {
                    return;
                }

                $user = $this->user();

                $session = app(ConversationRepositoryContract::class)
                    ->findSessionByConversationId($conversationId);

                if ($session === null) {
                    $validator->errors()->add('conversation_id', 'Conversación no encontrada.');

                    return;
                }

                if ($user === null && $session->user_id === null) {
                    return;
                }

                if ($user !== null && $session->user_id === $user->id) {
                    return;
                }

                $validator->errors()->add('conversation_id', 'No autorizado para usar esta conversación.');
            },
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:4000'],
            'conversation_id' => ['nullable', 'string', 'max:100'],
            'exercise_id' => ['required', 'integer', 'exists:exercises,id'],
            'source_code' => ['nullable', 'string', 'max:50000'],
            'output' => ['nullable', 'string', 'max:50000'],
        ];
    }
}
