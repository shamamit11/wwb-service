<?php

namespace App\Modules\ContentBriefs\Exceptions;

use RuntimeException;

class InvalidContentBriefStateTransitionException extends RuntimeException
{
    public function __construct(
        public readonly string $action,
        public readonly string $currentStatus,
        string $message,
    ) {
        parent::__construct($message);
    }
}
