<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Modules\Media\Services\Contracts\MediaReader;
use Illuminate\Http\Request;

class MediaResource extends ApiResource
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
            'source_type' => $this->resource->source_type,
            'source_url' => $this->resource->source_url,
            'attribution_text' => $this->resource->attribution_text,
            'mime_type' => $this->resource->mime_type,
            'file_size_bytes' => $this->resource->file_size_bytes,
            'width' => $this->resource->width,
            'height' => $this->resource->height,
            'alt_text' => $this->resource->alt_text,
            'caption' => $this->resource->caption,
            'url' => $reader->url($this->resource),
            'status' => $this->resource->status,
            'usage_count' => 0,
            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
