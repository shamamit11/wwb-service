<?php

namespace App\Modules\Seo\Services;

use App\Models\SeoMetadata;
use App\Modules\Seo\Data\UpdateSeoMetadataData;
use App\Modules\Seo\Repositories\SeoMetadataRepository;

class UpsertSeoMetadataService
{
    public function __construct(
        private readonly SeoMetadataRepository $metadata,
        private readonly ResolveSeoableTargetService $resolver,
    ) {}

    public function handle(string $seoableType, int $seoableId, UpdateSeoMetadataData $data): SeoMetadata
    {
        [$seoable] = $this->resolver->handle($seoableType, $seoableId);

        return $this->metadata->updateFor($seoable, $data);
    }
}
