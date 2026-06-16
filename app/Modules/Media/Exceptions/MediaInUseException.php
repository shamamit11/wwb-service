<?php

namespace App\Modules\Media\Exceptions;

use App\Modules\Media\Data\MediaUsageData;
use RuntimeException;

class MediaInUseException extends RuntimeException
{
    public function __construct(
        public readonly MediaUsageData $usage,
        string $message = 'Media is in use and cannot be deleted.',
    ) {
        parent::__construct($message);
    }
}
