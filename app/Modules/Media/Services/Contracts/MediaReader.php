<?php

namespace App\Modules\Media\Services\Contracts;

use App\Models\Media;

interface MediaReader
{
    public function findByUlid(string $ulid): ?Media;

    public function url(Media $media): string;

    public function read(Media $media): string;
}
