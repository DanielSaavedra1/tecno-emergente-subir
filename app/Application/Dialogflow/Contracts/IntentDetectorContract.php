<?php

declare(strict_types=1);

namespace App\Application\Dialogflow\Contracts;

use App\Application\Dialogflow\DTO\DetectedIntent;
use App\Application\Dialogflow\DTO\DetectIntentInput;

interface IntentDetectorContract
{
    public function detect(DetectIntentInput $input): DetectedIntent;
}
