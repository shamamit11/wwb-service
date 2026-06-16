<?php

namespace App\Modules\Seo\Repositories;

use App\Models\SeoMetadata;
use App\Modules\Seo\Data\CreateSeoMetadataData;
use App\Modules\Seo\Data\UpdateSeoMetadataData;
use Illuminate\Database\Eloquent\Model;

class EloquentSeoMetadataRepository implements SeoMetadataRepository
{
    public function createFor(Model $seoable, CreateSeoMetadataData $data): SeoMetadata
    {
        /** @var SeoMetadata $metadata */
        $metadata = $seoable->seo()->create($this->attributesFromCreate($data));

        return $metadata->load('ogImageMedia');
    }

    public function updateFor(Model $seoable, UpdateSeoMetadataData $data): SeoMetadata
    {
        $existing = $this->findFor($seoable);

        if ($existing === null) {
            return $this->createFor($seoable, new CreateSeoMetadataData(
                metaTitle: $data->metaTitle,
                metaDescription: $data->metaDescription,
                canonicalUrl: $data->canonicalUrl,
                robotsIndex: $data->robotsIndex,
                robotsFollow: $data->robotsFollow,
                ogTitle: $data->ogTitle,
                ogDescription: $data->ogDescription,
                ogImageMediaId: $data->ogImageMediaId,
                schemaType: $data->schemaType,
                schemaPayload: $data->schemaPayload,
                focusKeyword: $data->focusKeyword,
            ));
        }

        $existing->update($this->attributesFromUpdate($data));

        return $existing->refresh()->load('ogImageMedia');
    }

    public function findFor(Model $seoable): ?SeoMetadata
    {
        return SeoMetadata::query()
            ->with('ogImageMedia')
            ->where('seoable_type', $seoable->getMorphClass())
            ->where('seoable_id', $seoable->getKey())
            ->first();
    }

    public function deleteFor(Model $seoable): void
    {
        SeoMetadata::query()
            ->where('seoable_type', $seoable->getMorphClass())
            ->where('seoable_id', $seoable->getKey())
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFromCreate(CreateSeoMetadataData $data): array
    {
        return [
            'meta_title' => $data->metaTitle,
            'meta_description' => $data->metaDescription,
            'canonical_url' => $data->canonicalUrl,
            'robots_index' => $data->robotsIndex,
            'robots_follow' => $data->robotsFollow,
            'og_title' => $data->ogTitle,
            'og_description' => $data->ogDescription,
            'og_image_media_id' => $data->ogImageMediaId,
            'schema_type' => $data->schemaType,
            'schema_payload' => $data->schemaPayload,
            'focus_keyword' => $data->focusKeyword,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFromUpdate(UpdateSeoMetadataData $data): array
    {
        return [
            'meta_title' => $data->metaTitle,
            'meta_description' => $data->metaDescription,
            'canonical_url' => $data->canonicalUrl,
            'robots_index' => $data->robotsIndex,
            'robots_follow' => $data->robotsFollow,
            'og_title' => $data->ogTitle,
            'og_description' => $data->ogDescription,
            'og_image_media_id' => $data->ogImageMediaId,
            'schema_type' => $data->schemaType,
            'schema_payload' => $data->schemaPayload,
            'focus_keyword' => $data->focusKeyword,
        ];
    }
}
