<?php

namespace App\Modules\ContentTopics\Exceptions;

use RuntimeException;

class InvalidContentTopicStateTransitionException extends RuntimeException
{
    public function __construct(
        public readonly string $action,
        public readonly string $currentStatus,
        string $message,
    ) {
        parent::__construct($message);
    }
}
