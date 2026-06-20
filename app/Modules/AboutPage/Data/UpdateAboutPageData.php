<?php

namespace App\Modules\AboutPage\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateAboutPageData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $hero
     * @param  array<string, mixed>  $missionSection
     * @param  array<string, mixed>  $statsSection
     * @param  array<string, mixed>  $valuesSection
     * @param  array<string, mixed>  $teamSection
     * @param  array<string, mixed>  $seo
     */
    public function __construct(
        public int $updatedByUserId,
        public array $hero,
        public array $missionSection,
        public array $statsSection,
        public array $valuesSection,
        public array $teamSection,
        public array $seo,
    ) {}
}
