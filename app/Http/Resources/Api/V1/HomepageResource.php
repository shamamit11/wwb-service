<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Models\Homepage;
use Illuminate\Http\Request;

class HomepageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Homepage $homepage */
        $homepage = $this->resource;

        return [
            'hero' => $homepage->hero,
            'featured_editorial' => $homepage->featured_editorial,
            'guide_section' => $homepage->guide_section,
            'topic_section' => $homepage->topic_section,
            'promo_section' => $homepage->promo_section,
            'newsletter_section' => $homepage->newsletter_section,
            'seo' => $homepage->seo,
            'updated_at' => $homepage->updated_at?->toISOString(),
            'updated_by' => $this->whenLoaded('updatedBy', fn (): ?array => $homepage->updatedBy === null ? null : [
                'id' => $homepage->updatedBy->id,
                'name' => $homepage->updatedBy->name,
                'email' => $homepage->updatedBy->email,
            ]),
        ];
    }
}
