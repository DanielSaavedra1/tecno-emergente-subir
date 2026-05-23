<?php

namespace App\Exceptions;

use RuntimeException;

class Judge0UnavailableException extends RuntimeException
{
    public static function forUpstreamFailure(?string $message = null): self
    {
        return new self($message ?? 'Judge0 service is unavailable.');
    }
}
