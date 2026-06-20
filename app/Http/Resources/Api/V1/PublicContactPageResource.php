<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\ApiResource;
use App\Models\ContactPage;
use Illuminate\Http\Request;

class PublicContactPageResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ContactPage $contactPage */
        $contactPage = $this->resource;

        return [
            'hero' => $contactPage->hero,
            'contact_form' => $contactPage->contact_form,
            'contact_reasons' => $contactPage->contact_reasons,
            'seo' => $contactPage->seo,
        ];
    }
}
