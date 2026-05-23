<?php

namespace App\Application\Workspace\Services;

use App\Models\Exercise;
use App\Models\ExerciseAttempt;
use App\Models\UserExerciseProgress;
use Illuminate\Http\Request;

class WorkspacePayloadBuilder
{
    public function __construct(
        private LearningSidebarBuilder $sidebarBuilder,
        private WorkspaceFunctionRunner $functionRunner,
        private ExerciseAttemptRecorder $attemptRecorder,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request, ?Exercise $exercise): array
    {
        $exercise?->loadMissing('topic.level');
        $latestAttempt = $exercise === null
            ? null
            : $this->latestAttemptFor($request, $exercise);
        $progress = $exercise === null
            ? null
            : $this->progressFor($request, $exercise);

        return [
            'lmStudioModel' => config('services.lm_studio.model'),
            'currentExercise' => $exercise === null ? null : [
                'id' => $exercise->id,
                'number' => $exercise->number,
                'title' => $exercise->title,
                'description' => $exercise->description,
                'functionName' => $exercise->function_name,
                'starterCode' => $exercise->starter_code,
                'inputDescription' => $exercise->input_description,
                'outputDescription' => $exercise->output_description,
                'examples' => is_array($exercise->examples) ? $exercise->examples : [],
                'considerations' => is_array($exercise->considerations) ? $exercise->considerations : [],
            ],
            'learning' => $this->sidebarBuilder->build($request->user(), $exercise),
            'problemTitle' => $exercise?->title ?? 'Sin ejercicios',
            'problemDescription' => $exercise instanceof Exercise
                ? $this->exerciseProblemDescription($exercise)
                : 'Todavía no hay ejercicios cargados para resolver.',
            'problemStatus' => $this->problemStatus($exercise, $progress),
            'sourceCode' => $latestAttempt?->source_code ?? '',
            'output' => $latestAttempt === null
                ? '[Consola lista...]'
                : $this->attemptRecorder->resolveAttemptOutput($latestAttempt),
            'outputReport' => $latestAttempt === null ? null : $this->buildOutputReport($latestAttempt),
            'fileName' => 'solucion.py',
        ];
    }

    private function exerciseProblemDescription(Exercise $exercise): string
    {
        $sections = [$exercise->description];

        if (is_string($exercise->function_name) && trim($exercise->function_name) !== '') {
            $sections[] = '**Nombre de la función a utilizar:** `'.$exercise->function_name.'`';
        }

        if (is_string($exercise->input_description) && trim($exercise->input_description) !== '') {
            $sections[] = "### Entrada\n".$exercise->input_description;
        }

        if (is_string($exercise->output_description) && trim($exercise->output_description) !== '') {
            $sections[] = "### Salida\n".$exercise->output_description;
        }

        if (is_array($exercise->considerations) && $exercise->considerations !== []) {
            $sections[] = "### Consideraciones\n".collect($exercise->considerations)
                ->filter(fn (mixed $consideration): bool => is_string($consideration) && trim($consideration) !== '')
                ->map(fn (string $consideration): string => '- '.$consideration)
                ->implode(PHP_EOL);
        }

        if (is_array($exercise->examples) && $exercise->examples !== []) {
            $sections[] = "### Ejemplos\n".$this->examplesTable($exercise->examples);
        }

        return implode(PHP_EOL.PHP_EOL, array_filter($sections));
    }

    /**
     * @param  array<int, array<string, mixed>>  $examples
     */
    private function examplesTable(array $examples): string
    {
        $rows = [
            '| Parámetros | Resultado esperado |',
            '| --- | --- |',
        ];

        foreach ($examples as $example) {
            if (! is_array($example)) {
                continue;
            }

            $rows[] = '| '.$this->formatMarkdownTableValue($example['arguments'] ?? []).' | '.$this->formatMarkdownTableValue($example['expected'] ?? null).' |';
        }

        return implode(PHP_EOL, $rows);
    }

    private function formatMarkdownTableValue(mixed $value): string
    {
        return str_replace('|', '\\|', $this->functionRunner->formatResultValue($value));
    }

    private function latestAttemptFor(Request $request, Exercise $exercise): ?ExerciseAttempt
    {
        return ExerciseAttempt::query()
            ->where('user_id', $request->user()->id)
            ->where('exercise_id', $exercise->id)
            ->latest()
            ->first();
    }

    private function progressFor(Request $request, Exercise $exercise): ?UserExerciseProgress
    {
        return UserExerciseProgress::query()
            ->where('user_id', $request->user()->id)
            ->where('exercise_id', $exercise->id)
            ->first();
    }

    private function problemStatus(?Exercise $exercise, ?UserExerciseProgress $progress): string
    {
        if ($exercise === null) {
            return 'pending';
        }

        return $progress?->status === 'completed' ? 'done' : 'pending';
    }

    /**
     * @return array{status: string, summary: string, rows: array<int, array{case: int, label: string, input: string, expected: string, received: string, passed: bool}>, rawOutput?: string}|null
     */
    private function buildOutputReport(ExerciseAttempt $attempt): ?array
    {
        $functionResults = $attempt->function_results;

        if (! is_array($functionResults)) {
            $rawOutput = $attempt->stderr ?? $attempt->compile_output;

            if (is_string($rawOutput) && trim($rawOutput) !== '') {
                return [
                    'status' => 'error',
                    'summary' => 'Error de ejecución',
                    'rows' => [],
                    'rawOutput' => $rawOutput,
                ];
            }

            return null;
        }

        $totalCases = count($functionResults);
        $passedCases = count(array_filter($functionResults, fn (array $r): bool => ($r['passed'] ?? false) === true));
        $allPassed = $attempt->function_results_passed === true;

        $rows = [];
        foreach ($functionResults as $result) {
            $case = isset($result['case']) && is_numeric($result['case']) ? (int) $result['case'] : count($rows) + 1;
            $isPublic = ($result['is_public'] ?? false) === true;

            $rows[] = [
                'case' => $case,
                'label' => $isPublic ? "Caso {$case}" : "Caso oculto {$case}",
                'input' => $isPublic ? $this->functionRunner->formatResultValue($result['arguments'] ?? []) : 'Oculto',
                'expected' => $isPublic ? $this->functionRunner->formatResultValue($result['expected'] ?? null) : 'Oculto',
                'received' => $isPublic ? $this->functionRunner->formatResultValue($result['received'] ?? null) : 'Oculto',
                'passed' => ($result['passed'] ?? false) === true,
            ];
        }

        $summary = "{$passedCases}/{$totalCases} casos correctos";

        return [
            'status' => $allPassed ? 'success' : 'error',
            'summary' => $summary,
            'rows' => $rows,
        ];
    }
}
