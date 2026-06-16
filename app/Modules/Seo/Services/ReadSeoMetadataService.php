<?php

namespace App\Modules\Seo\Services;

use App\Models\SeoMetadata;
use App\Modules\Seo\Repositories\SeoMetadataRepository;

class ReadSeoMetadataService
{
    public function __construct(
        private readonly SeoMetadataRepository $metadata,
        private readonly ResolveSeoableTargetService $resolver,
        private readonly CanonicalUrlService $canonicalUrls,
    ) {}

    public function handle(string $seoableType, int $seoableId): SeoMetadata
    {
        [$seoable, $normalizedType] = $this->resolver->handle($seoableType, $seoableId);
        $existing = $this->metadata->findFor($seoable);

        if ($existing !== null) {
            if (($existing->canonical_url === null || $existing->canonical_url === '') && $existing->seoable !== null) {
                $existing->setAttribute('canonical_url', $this->canonicalUrls->for($existing->seoable));
            }

            return $existing;
        }

        $draft = new SeoMetadata([
            'robots_index' => true,
            'robots_follow' => true,
        ]);
        $draft->setRelation('seoable', $seoable);
        $draft->setAttribute('seoable_type', $normalizedType);
        $draft->setAttribute('seoable_id', $seoable->getKey());
        $draft->setAttribute('canonical_url', $this->canonicalUrls->for($seoable));

        return $draft;
    }
}
