<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Models\AboutPage;
use Illuminate\Http\Request;

class PublicAboutPageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var AboutPage $aboutPage */
        $aboutPage = $this->resource;

        return [
            'hero' => $aboutPage->hero,
            'mission_section' => $aboutPage->mission_section,
            'stats_section' => $aboutPage->stats_section,
            'values_section' => $aboutPage->values_section,
            'team_section' => $aboutPage->team_section,
            'seo' => $aboutPage->seo,
        ];
    }
}
