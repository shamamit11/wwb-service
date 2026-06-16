<?php

namespace App\Modules\Media\Repositories;

use App\Models\Media;
use App\Modules\Media\Data\CreateMediaData;
use App\Modules\Media\Data\UpdateMediaMetadataData;
use Illuminate\Database\Eloquent\Collection;

interface MediaRepository
{
    public function create(CreateMediaData $data): Media;

    public function updateMetadata(Media $media, UpdateMediaMetadataData $data): Media;

    public function findById(int $id): ?Media;

    public function findByUlid(string $ulid): ?Media;

    /**
     * @return Collection<int, Media>
     */
    public function getLatest(): Collection;

    public function markArchived(Media $media): Media;
}
