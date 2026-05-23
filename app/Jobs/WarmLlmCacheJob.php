<?php

namespace App\Jobs;

use App\Application\Chat\Support\ProblemContextBuilder;
use App\Infrastructure\AI\LmStudio\LmStudioGateway;
use App\Models\Exercise;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class WarmLlmCacheJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private int $exerciseId) {}

    public function handle(LmStudioGateway $gateway, ProblemContextBuilder $builder): void
    {
        \Illuminate\Support\Facades\Log::info('WarmLlmCacheJob ejecutando', ['exercise_id' => $this->exerciseId]);

        $exercise = Exercise::query()
            ->where('is_active', true)
            ->find($this->exerciseId);

        if ($exercise === null) {
            \Illuminate\Support\Facades\Log::info('WarmLlmCacheJob: ejercicio no encontrado');
            return;
        }

        $context = $builder->build($exercise);
        $gateway->warmCache([
            ['role' => 'system', 'content' => $builder->systemPromptWith($context)],
            ['role' => 'user',   'content' => 'ok'],
        ]);

        \Illuminate\Support\Facades\Log::info('WarmLlmCacheJob completado', ['exercise_id' => $this->exerciseId]);
    }
}