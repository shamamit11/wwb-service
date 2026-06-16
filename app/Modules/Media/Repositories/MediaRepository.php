<?php

namespace App\Modules\Media\Repositories;

use App\Models\Media;
use App\Modules\Media\Data\CreateMediaData;

interface MediaRepository
{
    public function create(CreateMediaData $data): Media;

    public function findById(int $id): ?Media;

    public function findByUlid(string $ulid): ?Media;

    public function markArchived(Media $media): Media;
}
