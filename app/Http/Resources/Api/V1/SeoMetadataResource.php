<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Models\Category;
use App\Models\KnowledgeBaseEntry;
use App\Models\Page;
use App\Models\Post;
use App\Modules\Media\Services\Contracts\MediaReader;
use Illuminate\Http\Request;

class SeoMetadataResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var MediaReader $reader */
        $reader = app(MediaReader::class);
        $seoable = $this->resource->seoable;

        return [
            'id' => $this->resource->id,
            'seoable_type' => match ($seoable::class) {
                Post::class => 'post',
                Category::class => 'category',
                Page::class => 'page',
                KnowledgeBaseEntry::class => 'knowledge_base_entry',
                default => $this->resource->seoable_type,
            },
            'seoable_id' => $this->resource->seoable_id,
            'meta_title' => $this->resource->meta_title,
            'meta_description' => $this->resource->meta_description,
            'canonical_url' => $this->resource->canonical_url,
            'robots_index' => (bool) $this->resource->robots_index,
            'robots_follow' => (bool) $this->resource->robots_follow,
            'og_title' => $this->resource->og_title,
            'og_description' => $this->resource->og_description,
            'og_image_media' => $this->whenLoaded('ogImageMedia', fn (): ?array => $this->resource->ogImageMedia === null ? null : [
                'id' => $this->resource->ogImageMedia->id,
                'ulid' => $this->resource->ogImageMedia->ulid,
                'original_filename' => $this->resource->ogImageMedia->original_filename,
                'mime_type' => $this->resource->ogImageMedia->mime_type,
                'alt_text' => $this->resource->ogImageMedia->alt_text,
                'caption' => $this->resource->ogImageMedia->caption,
                'url' => $reader->url($this->resource->ogImageMedia),
            ]),
            'schema_type' => $this->resource->schema_type,
            'schema_payload' => $this->resource->schema_payload ?? [],
            'focus_keyword' => $this->resource->focus_keyword,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
