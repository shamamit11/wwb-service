<?php

namespace App\Modules\Media\Services\Contracts;

use App\Models\Media;
use App\Modules\Media\Data\UploadMediaData;

interface MediaUploader
{
    public function upload(UploadMediaData $data): Media;
}
