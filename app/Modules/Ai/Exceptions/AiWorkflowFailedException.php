<?php

namespace App\Modules\Ai\Exceptions;

use RuntimeException;

class AiWorkflowFailedException extends RuntimeException
{
    public function __construct(
        public readonly string $workflow,
        public readonly ?string $agent,
        public readonly ?int $jobId,
        string $message,
    ) {
        parent::__construct($message);
    }
}
