<?php

namespace App\Modules\Media\Services;

use App\Modules\Media\Repositories\MediaRepository;
use Illuminate\Database\Eloquent\Collection;

class ListAdminMediaService
{
    public function __construct(
        private readonly MediaRepository $media,
    ) {}

    public function handle(): Collection
    {
        return $this->media->getLatest();
    }
}
