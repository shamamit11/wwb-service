<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Modules\Media\Data\UploadMediaData;
use App\Modules\Media\Services\Contracts\MediaUploader;
use Illuminate\Support\Collection;

class BatchUploadMediaService
{
    public function __construct(
        private readonly MediaUploader $uploader,
    ) {}

    /**
     * @param  list<UploadMediaData>  $uploads
     * @return Collection<int, Media>
     */
    public function handle(array $uploads): Collection
    {
        return collect($uploads)
            ->map(fn (UploadMediaData $upload): Media => $this->uploader->upload($upload));
    }
}
