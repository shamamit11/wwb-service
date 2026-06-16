<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Models\Template;
use Illuminate\Http\Request;

class PublicTemplateResource extends ApiResource
{
    /**
     * @return array<string, mixed>|null
     */
    public function toArray(Request $request): ?array
    {
        if ($this->resource->status !== Template::STATUS_ACTIVE) {
            return null;
        }

        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'template_type' => $this->resource->template_type,
            'description' => $this->resource->description,
        ];
    }
}
