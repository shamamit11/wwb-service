<?php

namespace App\Infrastructure\Ai\Exceptions;

use RuntimeException;
use Throwable;

class AiCallFailedException extends RuntimeException
{
    public static function fromThrowable(Throwable $throwable): self
    {
        $message = trim($throwable->getMessage());

        if ($message === '') {
            $message = $throwable::class;
        }

        return new self("AI text generation failed: {$message}", previous: $throwable);
    }
}
