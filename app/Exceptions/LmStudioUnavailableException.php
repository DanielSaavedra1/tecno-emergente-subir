<?php

namespace App\Exceptions;

use RuntimeException;

class LmStudioUnavailableException extends RuntimeException
{
    public static function forUpstreamFailure(?string $message = null): self
    {
        return new self($message ?? 'LM Studio service is unavailable.');
    }
}
