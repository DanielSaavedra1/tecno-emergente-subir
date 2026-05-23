<?php

declare(strict_types=1);

namespace App\Application\Dialogflow\DTO;

readonly class DetectedIntent
{
    /**
     * @param  string  $action  The Dialogflow action, e.g. "workspace.explain_concept"
     * @param  string  $intentName  The human-readable intent display name
     * @param  float  $confidence  Intent detection confidence (0.0 – 1.0)
     * @param  array<string, mixed>  $parameters  Extracted parameters, e.g. ["programming_concept" => ["return"]]
     * @param  bool  $passesThreshold  Whether confidence meets the configured threshold
     */
    public function __construct(
        public string $action,
        public string $intentName,
        public float $confidence,
        public array $parameters,
        public bool $passesThreshold,
    ) {}
}
