<?php

namespace App\Modules\Seo\Repositories;

use App\Models\SeoMetadata;
use App\Modules\Seo\Data\CreateSeoMetadataData;
use App\Modules\Seo\Data\UpdateSeoMetadataData;
use Illuminate\Database\Eloquent\Model;

interface SeoMetadataRepository
{
    public function createFor(Model $seoable, CreateSeoMetadataData $data): SeoMetadata;

    public function updateFor(Model $seoable, UpdateSeoMetadataData $data): SeoMetadata;

    public function findFor(Model $seoable): ?SeoMetadata;

    public function deleteFor(Model $seoable): void;
}
