<?php

namespace App\Modules\Seo\Services;

use App\Models\SeoMetadata;
use App\Modules\Seo\Data\UpdateSeoMetadataData;
use App\Modules\Seo\Repositories\SeoMetadataRepository;
use App\Support\AuditActivityLogger;

class UpsertSeoMetadataService
{
    public function __construct(
        private readonly SeoMetadataRepository $metadata,
        private readonly ResolveSeoableTargetService $resolver,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(string $seoableType, int $seoableId, UpdateSeoMetadataData $data): SeoMetadata
    {
        [$seoable] = $this->resolver->handle($seoableType, $seoableId);
        $existing = $this->metadata->findFor($seoable);
        $event = $existing === null ? 'created' : 'updated';
        $description = $existing === null ? 'seo_metadata.created' : 'seo_metadata.updated';
        $old = $existing === null ? [] : [
            'meta_title' => $existing->meta_title,
            'canonical_url' => $existing->canonical_url,
            'robots_index' => (bool) $existing->robots_index,
            'robots_follow' => (bool) $existing->robots_follow,
            'schema_type' => $existing->schema_type,
        ];

        $metadata = $this->metadata->updateFor($seoable, $data);

        $this->audit->log(
            logName: 'content',
            description: $description,
            event: $event,
            subject: $metadata,
            attributes: [
                'meta_title' => $metadata->meta_title,
                'canonical_url' => $metadata->canonical_url,
                'robots_index' => (bool) $metadata->robots_index,
                'robots_follow' => (bool) $metadata->robots_follow,
                'schema_type' => $metadata->schema_type,
            ],
            old: $old,
            context: [
                'seoable_type' => $metadata->seoable_type,
                'seoable_id' => $metadata->seoable_id,
            ],
        );

        return $metadata;
    }
}
