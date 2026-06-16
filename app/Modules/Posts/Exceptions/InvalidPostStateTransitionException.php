<?php

namespace App\Modules\Posts\Exceptions;

use RuntimeException;

class InvalidPostStateTransitionException extends RuntimeException
{
    public function __construct(
        public readonly string $action,
        public readonly string $currentStatus,
        string $message,
    ) {
        parent::__construct($message);
    }
}
