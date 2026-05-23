<?php

declare(strict_types=1);

namespace App\Infrastructure\AI\Dialogflow;

use App\Application\Dialogflow\Contracts\IntentDetectorContract;
use App\Application\Dialogflow\DTO\DetectedIntent;
use App\Application\Dialogflow\DTO\DetectIntentInput;
use App\Exceptions\DialogflowUnavailableException;
use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Protobuf\Internal\RepeatedField;

final class DialogflowIntentDetector implements IntentDetectorContract
{
    public function detect(DetectIntentInput $input): DetectedIntent
    {
        $projectId = (string) config('services.dialogflow.project_id');
        $languageCode = (string) config('services.dialogflow.language_code');
        $threshold = (float) config('services.dialogflow.confidence_threshold');

        $sessionId = $this->resolveSessionId($input->conversationId);

        $textInput = (new TextInput)
            ->setText($input->prompt)
            ->setLanguageCode($languageCode);

        $queryInput = (new QueryInput)
            ->setText($textInput);

        $session = SessionsClient::sessionName($projectId, $sessionId);

        $detectIntentRequest = (new DetectIntentRequest)
            ->setSession($session)
            ->setQueryInput($queryInput);

        $sessionsClient = $this->createSessionsClient();

        try {
            $response = $sessionsClient->detectIntent($detectIntentRequest);
        } catch (\Exception $exception) {
            throw DialogflowUnavailableException::forUpstreamFailure($exception->getMessage());
        } finally {
            $sessionsClient->close();
        }

        $queryResult = $response->getQueryResult();
        $intent = $queryResult->getIntent();
        $action = $queryResult->getAction() ?? '';
        $intentName = $intent !== null ? $intent->getDisplayName() : '';
        $confidence = $queryResult->getIntentDetectionConfidence();
        $parameters = $this->extractParameters($queryResult->getParameters());

        return new DetectedIntent(
            action: $action,
            intentName: $intentName,
            confidence: $confidence,
            parameters: $parameters,
            passesThreshold: $confidence >= $threshold,
        );
    }

    private function resolveSessionId(?string $conversationId): string
    {
        if ($conversationId !== null && $conversationId !== '') {
            return substr(hash('sha256', $conversationId), 0, 32);
        }

        return bin2hex(random_bytes(16));
    }

    /**
     * @return array<string, mixed>
     */
    private function extractParameters(?object $struct): array
    {
        if ($struct === null) {
            return [];
        }

        $fields = method_exists($struct, 'getFields') ? $struct->getFields() : null;

        if ($fields === null) {
            return [];
        }

        $parameters = [];

        /** @var RepeatedField $fields */
        foreach ($fields as $key => $value) {
            $parameters[$key] = $this->protobufValueToPhp($value);
        }

        return $parameters;
    }

    private function protobufValueToPhp(object $value): mixed
    {
        if (method_exists($value, 'getListValue') && $value->hasListValue()) {
            $list = $value->getListValue();
            $values = method_exists($list, 'getValues') ? $list->getValues() : [];

            $result = [];
            foreach ($values as $item) {
                $result[] = $this->protobufValueToPhp($item);
            }

            return $result;
        }

        if (method_exists($value, 'getStringValue')) {
            return $value->getStringValue();
        }

        if (method_exists($value, 'getNumberValue')) {
            return $value->getNumberValue();
        }

        if (method_exists($value, 'getBoolValue')) {
            return $value->getBoolValue();
        }

        if (method_exists($value, 'getStructValue') && $value->hasStructValue()) {
            return $this->extractParameters($value->getStructValue());
        }

        return null;
    }

    private function createSessionsClient(): SessionsClient
    {
        return new SessionsClient;
    }
}
