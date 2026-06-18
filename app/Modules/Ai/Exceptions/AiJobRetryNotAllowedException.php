<?php

namespace App\Modules\Ai\Exceptions;

use RuntimeException;

class AiJobRetryNotAllowedException extends RuntimeException
{
    public function __construct(
        public readonly string $currentStatus,
        string $message = 'Only failed AI jobs can be retried.',
    ) {
        parent::__construct($message);
    }
}
