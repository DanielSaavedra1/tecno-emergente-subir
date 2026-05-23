<?php

namespace App\Application\Workspace\Services;

use App\Models\Exercise;

class WorkspaceFunctionRunner
{
    private const RESULTS_START = '__TECNO_RESULTS_START__';

    private const RESULTS_END = '__TECNO_RESULTS_END__';

    public function codeForJudge(Exercise $exercise, string $code): string
    {
        if (! $this->usesFunctionRunner($exercise)) {
            return $code;
        }

        $testCases = $this->runnerTestCases($exercise);

        if ($testCases === []) {
            return $code;
        }

        $caseBlocks = [];

        foreach ($testCases as $index => $testCase) {
            $encodedArguments = json_encode($testCase['arguments'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $encodedArgumentsLiteral = json_encode($encodedArguments, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if (! is_string($encodedArguments) || ! is_string($encodedArgumentsLiteral)) {
                return $code;
            }

            $case = $index + 1;
            $isPublic = $testCase['is_public'] ? 'True' : 'False';
            $caseBlocks[] = "__tecno_run_case({$case}, json.loads({$encodedArgumentsLiteral}), {$isPublic})";
        }

        $functionName = $exercise->function_name;
        $resultsStart = self::RESULTS_START;
        $resultsEnd = self::RESULTS_END;
        $caseRunner = implode(PHP_EOL, $caseBlocks);

        return <<<PYTHON
{$code}

import contextlib
import io
import json

__tecno_results = []

def __tecno_run_case(__tecno_index, __tecno_arguments, __tecno_is_public):
    try:
        __tecno_output = io.StringIO()

        with contextlib.redirect_stdout(__tecno_output):
            __tecno_received = {$functionName}(*__tecno_arguments)

        __tecno_results.append({
            "case": __tecno_index,
            "received": __tecno_received,
            "is_public": bool(__tecno_is_public),
        })
    except Exception as __tecno_error:
        __tecno_results.append({
            "case": __tecno_index,
            "received": str(__tecno_error),
            "is_public": bool(__tecno_is_public),
            "error": str(__tecno_error),
        })

{$caseRunner}

print("{$resultsStart}")
print(json.dumps(__tecno_results, ensure_ascii=False))
print("{$resultsEnd}")
PYTHON;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function withFunctionExerciseResults(Exercise $exercise, array $payload): array
    {
        if (! $this->usesFunctionRunner($exercise)) {
            return $payload;
        }

        $results = $this->functionExerciseResults($payload);

        if ($results === null) {
            $payload['function_results_passed'] = false;

            if (empty($payload['stderr']) && empty($payload['compile_output'])) {
                $payload['stdout'] = 'No se pudieron leer los resultados de las pruebas.';
            }
            \Illuminate\Support\Facades\Log::info('Judge0 payload sin resultados:', [
                'stdout'         => $payload['stdout'] ?? null,
                'stderr'         => $payload['stderr'] ?? null,
                'compile_output' => $payload['compile_output'] ?? null,
                'status'         => $payload['status'] ?? null,
            ]);

            return $payload;
        }

        $gradedResults = $this->gradeFunctionResults($exercise, $results);

        $payload['function_results'] = $gradedResults;
        $payload['function_results_passed'] = $this->functionResultsPassed($gradedResults);
        $payload['stdout'] = $this->formatFunctionResults($gradedResults);

        return $payload;
    }

    public function formatResultValue(mixed $value): string
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : (string) $value;
    }

    private function usesFunctionRunner(Exercise $exercise): bool
    {
        return $exercise->exercise_type === 'function'
            && is_string($exercise->function_name)
            && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $exercise->function_name) === 1;
    }

    /**
     * @return array<int, array{arguments: array<int, mixed>, expected: mixed, is_public: bool, tolerance?: float}>
     */
    private function runnerTestCases(Exercise $exercise): array
    {
        if (! is_array($exercise->test_cases)) {
            return [];
        }

        $testCases = [];

        foreach ($exercise->test_cases as $testCase) {
            if (! is_array($testCase) || ! isset($testCase['arguments']) || ! is_array($testCase['arguments'])) {
                continue;
            }

            $normalized = [
                'arguments' => array_values($testCase['arguments']),
                'expected' => $testCase['expected'] ?? null,
                'is_public' => (bool) ($testCase['is_public'] ?? false),
            ];

            if (isset($testCase['tolerance']) && is_numeric($testCase['tolerance'])) {
                $normalized['tolerance'] = (float) $testCase['tolerance'];
            }

            $testCases[] = $normalized;
        }

        return $testCases;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>|null
     */
    private function functionExerciseResults(array $payload): ?array
    {
        $stdout = $this->stringPayloadValue($payload, 'stdout');

        if ($stdout === null) {
            return null;
        }

        $start = strrpos($stdout, self::RESULTS_START);

        if ($start === false) {
            return null;
        }

        $end = strpos($stdout, self::RESULTS_END, $start + strlen(self::RESULTS_START));

        if ($end === false || $end <= $start) {
            return null;
        }

        $jsonStart = $start + strlen(self::RESULTS_START);
        $json = trim(substr($stdout, $jsonStart, $end - $jsonStart));
        $results = json_decode($json, true);

        return is_array($results) ? $results : null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rawResults
     * @return array<int, array<string, mixed>>
     */
    private function gradeFunctionResults(Exercise $exercise, array $rawResults): array
    {
        $testCases = $this->runnerTestCases($exercise);
        $gradedResults = [];

        foreach ($testCases as $index => $testCase) {
            $rawResult = $rawResults[$index] ?? null;
            $isPublic = $testCase['is_public'] === true;
            $received = is_array($rawResult) ? ($rawResult['received'] ?? null) : null;
            $hasRunnerError = is_array($rawResult) && array_key_exists('error', $rawResult);
            $passed = is_array($rawResult) && ! $hasRunnerError && $this->resultsMatch(
                actual: $received,
                expected: $testCase['expected'],
                tolerance: $testCase['tolerance'] ?? null,
            );

            $gradedResults[] = [
                'case' => $index + 1,
                'arguments' => $isPublic ? $testCase['arguments'] : null,
                'expected' => $isPublic ? $testCase['expected'] : null,
                'received' => $isPublic ? $received : null,
                'passed' => $passed,
                'is_public' => $isPublic,
                'error' => $isPublic && $hasRunnerError ? $rawResult['error'] : null,
            ];
        }

        return $gradedResults;
    }

    private function resultsMatch(mixed $actual, mixed $expected, ?float $tolerance = null): bool
    {
        if (is_bool($actual) || is_bool($expected)) {
            return $actual === $expected;
        }

        if (is_numeric($actual) && is_numeric($expected)) {
            $tolerance ??= 1e-9;

            return abs((float) $actual - (float) $expected) <= $tolerance
                + $tolerance * abs((float) $expected);
        }

        if (is_array($actual) || is_array($expected)) {
            return $actual == $expected;
        }

        return trim((string) $actual) === trim((string) $expected);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    private function functionResultsPassed(array $results): bool
    {
        if ($results === []) {
            return false;
        }

        foreach ($results as $result) {
            if (($result['passed'] ?? false) !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    private function formatFunctionResults(array $results): string
    {
        $passedCount = 0;
        $lines = [];

        foreach ($results as $result) {
            $passed = ($result['passed'] ?? false) === true;
            $isPublic = ($result['is_public'] ?? false) === true;
            $case = isset($result['case']) && is_numeric($result['case']) ? (int) $result['case'] : count($lines) + 1;
            $label = $isPublic ? "Caso {$case}" : "Caso oculto {$case}";

            if ($passed) {
                $passedCount++;
                $lines[] = "{$label}: OK";

                continue;
            }

            $lines[] = "{$label}: Falló";

            if ($isPublic) {
                $lines[] = '  argumentos: '.$this->formatResultValue($result['arguments'] ?? []);
                $lines[] = '  esperado: '.$this->formatResultValue($result['expected'] ?? null);
                $lines[] = '  obtenido: '.$this->formatResultValue($result['received'] ?? null);
            }
        }

        array_unshift($lines, "Resultado: {$passedCount}/".count($results).' casos correctos');

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function stringPayloadValue(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }
}
