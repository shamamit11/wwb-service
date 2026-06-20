<?php

namespace App\Modules\ContactPage\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateContactPageData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $hero
     * @param  array<string, mixed>  $contactForm
     * @param  array<string, mixed>  $contactReasons
     * @param  array<string, mixed>  $seo
     */
    public function __construct(
        public int $updatedByUserId,
        public array $hero,
        public array $contactForm,
        public array $contactReasons,
        public array $seo,
    ) {}
}
