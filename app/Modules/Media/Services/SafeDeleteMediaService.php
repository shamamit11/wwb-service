<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Modules\Media\Exceptions\MediaInUseException;
use App\Modules\Media\Services\Contracts\MediaDeleter;

class SafeDeleteMediaService
{
    public function __construct(
        private readonly MediaUsageService $usage,
        private readonly MediaDeleter $deleter,
    ) {}

    public function handle(Media $media): Media
    {
        $usage = $this->usage->usageFor($media);

        if ($usage->inUse()) {
            throw new MediaInUseException($usage);
        }

        return $this->deleter->delete($media);
    }
}
