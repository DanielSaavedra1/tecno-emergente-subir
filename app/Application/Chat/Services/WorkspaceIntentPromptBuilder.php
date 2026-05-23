<?php

declare(strict_types=1);

namespace App\Application\Chat\Services;

use App\Application\Chat\Contracts\IntentPromptBuilderContract;
use App\Application\Chat\DTO\ExerciseContext;
use App\Application\Dialogflow\DTO\DetectedIntent;

final class WorkspaceIntentPromptBuilder implements IntentPromptBuilderContract
{
    private const INTENTS_THAT_NEED_CODE = [
        'workspace.review_code',
        'workspace.debug_error',
    ];

    private const INTENTS_THAT_NEED_OUTPUT = [
        'workspace.debug_error',
    ];

    /**
     * Prompt templates keyed by Dialogflow action.
     * Fill these with real prompts later — they are intentionally empty for now.
     *
     * @var array<string, string>
     */
    private const TEMPLATES = [
        'workspace.explain_statement' => '',
        'workspace.give_hint' => '',
        'workspace.review_code' => '',
        'workspace.debug_error' => '',
        'workspace.explain_concept' => '',
        'workspace.request_example' => '',
    ];

    private const FALLBACK_ACTION = 'workspace.explain_statement';

    public function build(string $userMessage, DetectedIntent $intent, ExerciseContext $exercise): string
    {
        $template = $this->resolveTemplate($intent->action);
        $parametersBlock = $this->formatParameters($intent->parameters);

        $lines = [
            "Intención: {$intent->action}",
            "Mensaje del usuario: {$userMessage}",
        ];

        if ($parametersBlock !== '') {
            $lines[] = "Parámetros: {$parametersBlock}";
        }

        if ($template !== '') {
            $lines[] = "Prompt fijo: {$template}";
        }

        if (in_array($intent->action, self::INTENTS_THAT_NEED_CODE, true) && $exercise->sourceCode !== null && $exercise->sourceCode !== '') {
            $lines[] = "Código del estudiante:\n{$exercise->sourceCode}";
        }

        if (in_array($intent->action, self::INTENTS_THAT_NEED_OUTPUT, true) && $exercise->output !== null && $exercise->output !== '') {
            $lines[] = "Salida obtenida:\n{$exercise->output}";
        }

        return implode("\n", $lines);
    }

    private function resolveTemplate(string $action): string
    {
        return self::TEMPLATES[$action] ?? self::TEMPLATES[self::FALLBACK_ACTION];
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function formatParameters(array $parameters): string
    {
        if ($parameters === []) {
            return '';
        }

        $formatted = [];

        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $formatted[] = "{$key}: ".implode(', ', $value);
            } else {
                $formatted[] = "{$key}: {$value}";
            }
        }

        return implode('; ', $formatted);
    }
}
