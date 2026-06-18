<?php

namespace App\Modules\Posts\Exceptions;

use RuntimeException;

class BlogDraftGenerationNotAllowedException extends RuntimeException
{
    public function __construct(
        public readonly string $briefStatus,
        string $message,
    ) {
        parent::__construct($message);
    }
}
