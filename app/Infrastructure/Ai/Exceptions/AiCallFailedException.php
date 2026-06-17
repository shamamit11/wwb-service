<?php

namespace App\Infrastructure\Ai\Exceptions;

use RuntimeException;
use Throwable;

class AiCallFailedException extends RuntimeException
{
    public static function fromThrowable(Throwable $throwable): self
    {
        return new self('AI text generation failed.', previous: $throwable);
    }
}
