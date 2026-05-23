<?php

namespace App\Infrastructure\AI\Judge0;

use App\Exceptions\Judge0UnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class Judge0Gateway
{
    /**
     * @return array<string, mixed>
     */
    public function run(string $code, int $languageId): array
    {
        $baseUrl = rtrim((string) config('services.judge0.base_url'), '/');

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->connectTimeout((int) config('services.judge0.connect_timeout'))
                ->timeout((int) config('services.judge0.timeout'))
                ->retry(
                    times: (int) config('services.judge0.retries'),
                    sleepMilliseconds: 200,
                )
                ->post("{$baseUrl}/submissions?base64_encoded=false&wait=true", [
                    'source_code' => $code,
                    'language_id' => $languageId,
                ])
                ->throw();
        } catch (RequestException|ConnectionException $exception) {
            throw Judge0UnavailableException::forUpstreamFailure($exception->getMessage());
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }
}
