<?php

namespace App\Infrastructure\AI\LmStudio;

use App\Application\Chat\Contracts\ChatGatewayContract;
use App\Exceptions\LmStudioUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class LmStudioGateway implements ChatGatewayContract
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function generateReply(array $messages): string
    {
        $client = Http::baseUrl((string) config('services.lm_studio.base_url'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout((int) config('services.lm_studio.connect_timeout'))
            ->timeout((int) config('services.lm_studio.timeout'))
            ->retry(
                times: (int) config('services.lm_studio.retries'),
                sleepMilliseconds: 200,
            );

        if (filled(config('services.lm_studio.api_key'))) {
            $client = $client->withToken((string) config('services.lm_studio.api_key'));
        }

        try {
            $response = $client
                ->post('/chat/completions', [
                    'model' => (string) config('services.lm_studio.model'),
                    'messages' => array_values($messages),
                    //'tools' => [],
                    'temperature' => 0.2,
                    'max_tokens' => (int) config('services.lm_studio.max_tokens'),
                    'cache_prompt' => true,
                ])
                ->throw();
        } catch (RequestException|ConnectionException $exception) {
            throw LmStudioUnavailableException::forUpstreamFailure($exception->getMessage());
        }

        $reply = $response->json('choices.0.message.content');

        if (! is_string($reply) || trim($reply) === '') {
            throw LmStudioUnavailableException::forUpstreamFailure('LM Studio returned an empty response.');
        }

        return trim($reply);
    }

   public function warmCache(array $messages): void
   {
    try {
        Http::baseUrl((string) config('services.lm_studio.base_url'))
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->post('/chat/completions', [
                'model'        => (string) config('services.lm_studio.model'),
                'messages'     => $messages,
                'max_tokens'   => 1,
                'temperature'  => 0.2,
                'cache_prompt' => true,
            ]);
    } catch (\Throwable) {
        // Silencioso — es solo precalentamiento
    }
    }
}
