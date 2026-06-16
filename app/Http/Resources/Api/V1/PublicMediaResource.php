<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Modules\Media\Services\Contracts\MediaReader;
use Illuminate\Http\Request;

class PublicMediaResource extends ApiResource
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
            'mime_type' => $this->resource->mime_type,
            'width' => $this->resource->width,
            'height' => $this->resource->height,
            'alt_text' => $this->resource->alt_text,
            'caption' => $this->resource->caption,
            'url' => $reader->url($this->resource),
        ];
    }
}
