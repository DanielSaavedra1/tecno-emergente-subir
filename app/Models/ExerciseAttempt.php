<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'exercise_id', 'source_code', 'language_id', 'status', 'judge0_status_id', 'stdout', 'stderr', 'compile_output', 'function_results', 'function_results_passed', 'execution_time', 'memory', 'submitted_at'])]
class ExerciseAttempt extends Model
{
    protected function casts(): array
    {
        return [
            'function_results' => 'array',
            'function_results_passed' => 'boolean',
            'submitted_at' => 'datetime',
            'execution_time' => 'decimal:3',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
