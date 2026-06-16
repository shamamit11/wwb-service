<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Modules\Media\Repositories\MediaRepository;
use App\Modules\Media\Services\Contracts\MediaDeleter;
use App\Modules\Media\Services\Contracts\MediaStorage;
use App\Support\AuditActivityLogger;

class DeleteMediaService implements MediaDeleter
{
    public function __construct(
        private readonly MediaRepository $media,
        private readonly MediaStorage $storage,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function delete(Media $media): Media
    {
        $old = [
            'status' => $media->status,
            'object_key' => $media->object_key,
        ];

        $this->storage->delete($media->object_key);

        $archived = $this->media->markArchived($media);

        $this->audit->log(
            logName: 'content',
            description: 'media.deleted',
            event: 'deleted',
            subject: $archived,
            attributes: [
                'status' => $archived->status,
                'object_key' => $archived->object_key,
            ],
            old: $old,
        );

        return $archived;
    }
}
