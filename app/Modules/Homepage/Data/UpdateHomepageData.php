<?php

namespace App\Modules\Homepage\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateHomepageData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $hero
     * @param  array<string, mixed>  $featuredEditorial
     * @param  array<string, mixed>  $guideSection
     * @param  array<string, mixed>  $topicSection
     * @param  array<string, mixed>  $promoSection
     * @param  array<string, mixed>  $newsletterSection
     * @param  array<string, mixed>  $seo
     */
    public function __construct(
        public int $updatedByUserId,
        public array $hero,
        public array $featuredEditorial,
        public array $guideSection,
        public array $topicSection,
        public array $promoSection,
        public array $newsletterSection,
        public array $seo,
    ) {}
}
