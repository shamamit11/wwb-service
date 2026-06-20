<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Models\SiteSettings;
use Illuminate\Http\Request;

class SiteSettingsResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SiteSettings $siteSettings */
        $siteSettings = $this->resource;

        return [
            'footer' => $siteSettings->footer,
            'updated_at' => $siteSettings->updated_at?->toISOString(),
            'updated_by' => $this->whenLoaded('updatedBy', fn (): ?array => $siteSettings->updatedBy === null ? null : [
                'id' => $siteSettings->updatedBy->id,
                'name' => $siteSettings->updatedBy->name,
                'email' => $siteSettings->updatedBy->email,
            ]),
        ];
    }
}
