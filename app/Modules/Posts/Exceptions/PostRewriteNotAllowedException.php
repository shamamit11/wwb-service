<?php

namespace App\Modules\Posts\Exceptions;

use RuntimeException;

class PostRewriteNotAllowedException extends RuntimeException
{
    public function __construct(
        public readonly string $postStatus,
        string $message,
    ) {
        parent::__construct($message);
    }
}
