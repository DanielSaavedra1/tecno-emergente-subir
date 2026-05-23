<?php

namespace App\Application\Workspace\Services;

use App\Models\Exercise;
use App\Models\LearningLevel;
use App\Models\User;
use App\Models\UserExerciseProgress;

class LearningSidebarBuilder
{
    public function firstActiveExercise(): ?Exercise
    {
        $levels = LearningLevel::query()
            ->where('is_active', true)
            ->with([
                'topics' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('position'),
                'topics.exercises' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('position'),
            ])
            ->orderBy('position')
            ->get();

        foreach ($levels as $level) {
            foreach ($level->topics as $topic) {
                $exercise = $topic->exercises->first();

                if ($exercise instanceof Exercise) {
                    return $exercise->loadMissing('topic.level');
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function build(User $user, ?Exercise $activeExercise): array
    {
        $level = $activeExercise?->topic?->level ?? LearningLevel::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->first();

        if ($level === null) {
            return [
                'currentLevel' => [
                    'id' => null,
                    'number' => 1,
                    'title' => 'Sin ejercicios',
                    'progress' => 0,
                    'total' => 0,
                    'exercises' => [],
                ],
                'activeExerciseId' => null,
                'previousExerciseHref' => null,
                'nextExerciseHref' => null,
            ];
        }

        $level->load([
            'topics' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('position'),
            'topics.exercises' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('position'),
        ]);

        $exercises = $level->topics
            ->flatMap(fn ($topic) => $topic->exercises)
            ->values();
        $progressByExercise = UserExerciseProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('exercise_id', $exercises->pluck('id'))
            ->get()
            ->keyBy('exercise_id');
        $completedExercises = $progressByExercise
            ->filter(fn (UserExerciseProgress $progress): bool => $progress->status === 'completed')
            ->count();
        $totalExercises = $exercises->count();

        return [
            'currentLevel' => [
                'id' => $level->id,
                'number' => $level->position,
                'title' => $level->title,
                'progress' => $completedExercises,
                'total' => $totalExercises,
                'exercises' => $exercises
                    ->map(fn (Exercise $exercise): array => [
                        'id' => $exercise->id,
                        'number' => $exercise->number,
                        'title' => $exercise->title,
                        'href' => route('workspace.exercises.show', ['exercise' => $exercise]),
                        'status' => $progressByExercise->get($exercise->id)?->status === 'completed'
                            ? 'done'
                            : 'pending',
                        'isActive' => $activeExercise?->is($exercise) ?? false,
                    ])
                    ->all(),
            ],
            'activeExerciseId' => $activeExercise?->id,
            'previousExerciseHref' => $this->adjacentLevelFirstExerciseHref($level, '<', 'desc'),
            'nextExerciseHref' => $this->adjacentLevelFirstExerciseHref($level, '>', 'asc'),
        ];
    }

    /**
     * @param  '<'|'>'  $operator
     * @param  'asc'|'desc'  $direction
     */
    private function adjacentLevelFirstExerciseHref(LearningLevel $level, string $operator, string $direction): ?string
    {
        $levels = LearningLevel::query()
            ->where('is_active', true)
            ->where('position', $operator, $level->position)
            ->with([
                'topics' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('position'),
                'topics.exercises' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('position'),
            ])
            ->orderBy('position', $direction)
            ->get();

        foreach ($levels as $adjacentLevel) {
            $exercise = $adjacentLevel->topics
                ->flatMap(fn ($topic) => $topic->exercises)
                ->first();

            if ($exercise instanceof Exercise) {
                return route('workspace.exercises.show', ['exercise' => $exercise]);
            }
        }

        return null;
    }
}
