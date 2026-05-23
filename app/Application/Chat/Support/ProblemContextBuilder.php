<?php

namespace App\Application\Chat\Support;

use App\Models\Exercise;

class ProblemContextBuilder
{
    public function build(Exercise $exercise): string
    {
        $lines = [];

        $lines[] = "EJERCICIO: {$exercise->title}";
        //$lines[] = "DESCRIPCIÓN:\n{$exercise->description}";

        if (filled($exercise->input_description)) {
            $lines[] = "ENTRADA: {$exercise->input_description}";
        }

        if (filled($exercise->output_description)) {
            $lines[] = "SALIDA ESPERADA: {$exercise->output_description}";
        }

        if (! empty($exercise->considerations)) {
            $lines[] = "CONSIDERACIONES:\n- " . implode("\n- ", $exercise->considerations);
        }

        if (! empty($exercise->examples)) {
            $examples = collect($exercise->examples)
                ->map(fn($e) => 'Entrada: ' . json_encode($e['arguments']) . ' → Esperado: ' . json_encode($e['expected']))
                ->implode("\n");
            $lines[] = "EJEMPLOS PÚBLICOS:\n{$examples}";
        }

        return implode("\n\n", $lines);
    }

    public function systemPromptWith(string $context): string
    {
        return "Eres un tutor de programación conciso. 
        Guía al estudiante sin dar la solución completa o en código. 
        No repitas el enunciado. 
        Responde en máximo 3 oraciones.\n\n{$context}";
    }
}