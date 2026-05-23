<?php

namespace App\Application\Workspace\Services;

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\User;
use App\Models\UserExerciseProgress;
use Illuminate\Support\Str;

class ExerciseAttemptRecorder
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function record(User $user, Exercise $exercise, string $code, int $languageId, array $payload): ExerciseAttempt
    {
        $accepted = $this->isAccepted($payload);
        $functionResults = $payload['function_results'] ?? null;
        $functionResultsPassed = $payload['function_results_passed'] ?? null;
        $attempt = ExerciseAttempt::query()->create([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'source_code' => $code,
            'language_id' => $languageId,
            'status' => $accepted ? 'accepted' : 'failed',
            'judge0_status_id' => $this->judge0StatusId($payload),
            'stdout' => $this->stringPayloadValue($payload, 'stdout') ?? $this->stringPayloadValue($payload, 'output'),
            'stderr' => $this->stringPayloadValue($payload, 'stderr') ?? $this->stringPayloadValue($payload, 'error'),
            'compile_output' => $this->stringPayloadValue($payload, 'compile_output'),
            'function_results' => is_array($functionResults) ? $functionResults : null,
            'function_results_passed' => is_bool($functionResultsPassed) ? $functionResultsPassed : null,
            'execution_time' => isset($payload['time']) && is_numeric($payload['time']) ? (float) $payload['time'] : null,
            'memory' => isset($payload['memory']) && is_numeric($payload['memory']) ? (int) $payload['memory'] : null,
            'submitted_at' => now(),
        ]);

        $progress = UserExerciseProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
        ]);

        $progress->last_attempt_id = $attempt->id;

        if ($accepted) {
            $progress->status = 'completed';
            $progress->completed_at ??= now();
        } elseif ($progress->status !== 'completed') {
            $progress->status = 'in_progress';
            $progress->completed_at = null;
        }

        $progress->save();

        return $attempt;
    }

    public function resolveAttemptOutput(ExerciseAttempt $attempt): string
    {
        foreach ([$attempt->stdout, $attempt->stderr, $attempt->compile_output] as $value) {
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return '[Sin salida]';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function judge0StatusId(array $payload): ?int
    {
        $status = $payload['status'] ?? null;

        if (! is_array($status) || ! isset($status['id'])) {
            return null;
        }

        return is_numeric($status['id']) ? (int) $status['id'] : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function stringPayloadValue(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function isAccepted(array $payload): bool
    {
        $status = $payload['status'] ?? null;
        $statusId = null;
        $statusDescription = '';

        if (is_array($status)) {
            $statusId = isset($status['id']) ? (int) $status['id'] : null;
            $statusDescription = isset($status['description']) && is_string($status['description'])
                ? Str::lower($status['description'])
                : '';
        }

        $judge0Accepted = ($statusId === 3 || Str::contains($statusDescription, 'accepted'))
            && empty($payload['stderr'])
            && empty($payload['compile_output']);

        if (! $judge0Accepted) {
            return false;
        }

        if (array_key_exists('function_results_passed', $payload)) {
            return $payload['function_results_passed'] === true;
        }

        return true;
    }
}
