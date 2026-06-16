<?php

namespace App\Modules\Seo\Services;

use App\Models\SeoMetadata;
use App\Modules\Seo\Repositories\SeoMetadataRepository;

class ReadSeoMetadataService
{
    public function __construct(
        private readonly SeoMetadataRepository $metadata,
        private readonly ResolveSeoableTargetService $resolver,
    ) {}

    public function handle(string $seoableType, int $seoableId): SeoMetadata
    {
        [$seoable, $normalizedType] = $this->resolver->handle($seoableType, $seoableId);
        $existing = $this->metadata->findFor($seoable);

        if ($existing !== null) {
            return $existing;
        }

        $draft = new SeoMetadata([
            'robots_index' => true,
            'robots_follow' => true,
        ]);
        $draft->setRelation('seoable', $seoable);
        $draft->setAttribute('seoable_type', $normalizedType);
        $draft->setAttribute('seoable_id', $seoable->getKey());

        return $draft;
    }
}
