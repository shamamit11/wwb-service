<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Modules\Media\Data\UpdateMediaMetadataData;
use App\Modules\Media\Repositories\MediaRepository;

class UpdateMediaMetadataService
{
    public function __construct(
        private readonly MediaRepository $media,
    ) {}

    public function handle(Media $media, UpdateMediaMetadataData $data): Media
    {
        return $this->media->updateMetadata($media, $data);
    }
}
