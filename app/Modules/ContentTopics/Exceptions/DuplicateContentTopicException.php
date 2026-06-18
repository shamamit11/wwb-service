<?php

namespace App\Modules\ContentTopics\Exceptions;

use RuntimeException;

class DuplicateContentTopicException extends RuntimeException
{
    public function __construct(
        public readonly string $title,
        public readonly string $cluster,
        string $message,
    ) {
        parent::__construct($message);
    }
}
