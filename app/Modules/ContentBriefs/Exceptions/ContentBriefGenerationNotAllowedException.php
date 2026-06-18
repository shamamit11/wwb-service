<?php

namespace App\Modules\ContentBriefs\Exceptions;

use RuntimeException;

class ContentBriefGenerationNotAllowedException extends RuntimeException
{
    public function __construct(
        public readonly string $topicStatus,
        string $message,
    ) {
        parent::__construct($message);
    }
}
