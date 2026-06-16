<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Modules\Media\Data\UpdateMediaMetadataData;
use App\Modules\Media\Repositories\MediaRepository;
use App\Support\AuditActivityLogger;

class UpdateMediaMetadataService
{
    public function __construct(
        private readonly MediaRepository $media,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(Media $media, UpdateMediaMetadataData $data): Media
    {
        $old = [
            'alt_text' => $media->alt_text,
            'caption' => $media->caption,
            'source_url' => $media->source_url,
            'attribution_text' => $media->attribution_text,
        ];

        $updated = $this->media->updateMetadata($media, $data);

        $this->audit->log(
            logName: 'content',
            description: 'media.updated',
            event: 'updated',
            subject: $updated,
            attributes: [
                'alt_text' => $updated->alt_text,
                'caption' => $updated->caption,
                'source_url' => $updated->source_url,
                'attribution_text' => $updated->attribution_text,
                'status' => $updated->status,
            ],
            old: $old,
        );

        return $updated;
    }
}
