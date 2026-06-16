<?php

namespace App\Modules\Media\Services\Contracts;

use App\Models\Media;

interface MediaDeleter
{
    public function delete(Media $media): Media;
}
