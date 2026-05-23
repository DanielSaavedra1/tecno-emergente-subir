<?php

namespace App\Application\Chat\UseCases;

use App\Application\Chat\Contracts\ChatGatewayContract;
use App\Application\Chat\Contracts\ConversationRepositoryContract;
use App\Application\Chat\Contracts\IntentPromptBuilderContract;
use App\Application\Chat\DTO\ExerciseContext;
use App\Application\Chat\DTO\SendChatMessageInput;
use App\Application\Chat\DTO\SendChatMessageOutput;
use App\Application\Chat\Support\ProblemContextBuilder;
use App\Application\Dialogflow\Contracts\IntentDetectorContract;
use App\Application\Dialogflow\DTO\DetectIntentInput;
use App\Models\Exercise;
use Illuminate\Support\Facades\Log;

class SendChatMessageUseCase
{
    public function __construct(
        private ChatGatewayContract $chatGateway,
        private ConversationRepositoryContract $conversationRepository,
        private IntentDetectorContract $intentDetector,
        private IntentPromptBuilderContract $promptBuilder,
        private ProblemContextBuilder $contextBuilder,
    ) {}

    public function handle(SendChatMessageInput $input): SendChatMessageOutput
    {
        $session = $this->conversationRepository->findOrCreateSession(
            $input->conversationId,
            $input->userId,
            $input->isAuthenticated,
        );

        $chatSessionId = $session['chat_session_id'];
        $conversationId = $session['conversation_id'];

        $detectedIntent = $this->intentDetector->detect(
            new DetectIntentInput(
                prompt: $input->prompt,
                conversationId: $conversationId,
            ),
        );

        Log::info('Dialogflow intent detected', [
            'action' => $detectedIntent->action,
            'intent' => $detectedIntent->intentName,
            'confidence' => $detectedIntent->confidence,
            'parameters' => $detectedIntent->parameters,
            'conversation_id' => $conversationId,
        ]);

        $exercise = Exercise::findOrFail($input->exerciseId);

        $exerciseContext = new ExerciseContext(
            title: $exercise->title,
            description: $exercise->description ?? '',
            functionName: $exercise->function_name,
            inputDescription: $exercise->input_description,
            outputDescription: $exercise->output_description,
            examples: $exercise->examples,
            considerations: $exercise->considerations,
            sourceCode: $input->sourceCode,
            output: $input->output,
        );

        $systemMessage = $this->contextBuilder->systemPromptWith(
            $this->contextBuilder->build($exercise),
        );

        $userPrompt = $this->promptBuilder->build($input->prompt, $detectedIntent, $exerciseContext);

        $context = $this->conversationRepository->getRecentMessages($chatSessionId);

        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ...$this->messagesForModel($context),
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $reply = $this->chatGateway->generateReply($messages);

        $this->conversationRepository->persistExchange($chatSessionId, $input->prompt, $reply);

        $memoryExchanges = intdiv(
            $this->conversationRepository->countMessagesByConversation($conversationId), 2
        );

        return new SendChatMessageOutput(
            reply: $reply,
            conversationId: $conversationId,
            memoryExchanges: $memoryExchanges,
        );
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<int, array{role: string, content: string}>
     */
    private function messagesForModel(array $messages): array
    {
        $normalized = [];
        $expectedRole = 'user';

        foreach ($messages as $message) {
            if (! in_array($message['role'], ['user', 'assistant'], true)) {
                continue;
            }

            if ($message['role'] !== $expectedRole) {
                continue;
            }

            $normalized[] = $message;
            $expectedRole = $expectedRole === 'user' ? 'assistant' : 'user';
        }

        if (($normalized[array_key_last($normalized)]['role'] ?? null) === 'user') {
            array_pop($normalized);
        }

        return $normalized;
    }
}
