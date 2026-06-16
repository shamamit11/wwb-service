<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Modules\Media\Repositories\MediaRepository;
use App\Modules\Media\Services\Contracts\MediaReader;
use App\Modules\Media\Services\Contracts\MediaStorage;

class ReadMediaService implements MediaReader
{
    public function __construct(
        private readonly MediaRepository $media,
        private readonly MediaStorage $storage,
    ) {}

    public function findByUlid(string $ulid): ?Media
    {
        return $this->media->findByUlid($ulid);
    }

    public function url(Media $media): string
    {
        return $this->storage->url($media->object_key);
    }

    public function read(Media $media): string
    {
        return $this->storage->read($media->object_key);
    }
}
