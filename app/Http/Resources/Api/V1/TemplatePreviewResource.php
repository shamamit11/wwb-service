<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use Illuminate\Http\Request;

class TemplatePreviewResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'template' => (new TemplateResource($this->resource['template']))->toArray($request),
            'preview' => $this->resource['preview'],
        ];
    }
}
