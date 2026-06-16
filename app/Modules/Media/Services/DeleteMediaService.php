<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Modules\Media\Repositories\MediaRepository;
use App\Modules\Media\Services\Contracts\MediaDeleter;
use App\Modules\Media\Services\Contracts\MediaStorage;

class DeleteMediaService implements MediaDeleter
{
    public function __construct(
        private readonly MediaRepository $media,
        private readonly MediaStorage $storage,
    ) {}

    public function delete(Media $media): Media
    {
        $this->storage->delete($media->object_key);

        return $this->media->markArchived($media);
    }
}
