<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class DialogflowUnavailableException extends Exception
{
    public static function forUpstreamFailure(string $reason): self
    {
        return new self("Dialogflow service unavailable: {$reason}");
    }
}
