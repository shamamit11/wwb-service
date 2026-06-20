<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Models\SiteSettings;
use Illuminate\Http\Request;

class PublicSiteSettingsResource extends ApiResource
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
        ];
    }
}
