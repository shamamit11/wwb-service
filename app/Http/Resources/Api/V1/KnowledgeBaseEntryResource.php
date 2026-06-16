<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Modules\Media\Services\Contracts\MediaReader;
use Illuminate\Http\Request;

class KnowledgeBaseEntryResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var MediaReader $reader */
        $reader = app(MediaReader::class);

        return [
            'id' => $this->resource->id,
            'ulid' => $this->resource->ulid,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'entry_type' => $this->resource->entry_type,
            'status' => $this->resource->status,
            'summary' => $this->resource->summary,
            'content_markdown' => $this->resource->content_markdown,
            'source_url' => $this->resource->source_url,
            'metadata' => $this->resource->metadata ?? [],
            'created_by' => $this->whenLoaded('createdBy', fn (): array => [
                'id' => $this->resource->createdBy->id,
                'name' => $this->resource->createdBy->name,
                'email' => $this->resource->createdBy->email,
            ]),
            'updated_by' => $this->whenLoaded('updatedBy', fn (): ?array => $this->resource->updatedBy === null ? null : [
                'id' => $this->resource->updatedBy->id,
                'name' => $this->resource->updatedBy->name,
                'email' => $this->resource->updatedBy->email,
            ]),
            'featured_media' => $this->whenLoaded('featuredMedia', fn (): ?array => $this->resource->featuredMedia === null ? null : [
                'id' => $this->resource->featuredMedia->id,
                'ulid' => $this->resource->featuredMedia->ulid,
                'original_filename' => $this->resource->featuredMedia->original_filename,
                'mime_type' => $this->resource->featuredMedia->mime_type,
                'alt_text' => $this->resource->featuredMedia->alt_text,
                'caption' => $this->resource->featuredMedia->caption,
                'url' => $reader->url($this->resource->featuredMedia),
            ]),
            'linked_posts' => $this->resource->linkedPosts(),
            'linked_topics' => $this->resource->linkedTopics(),
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
