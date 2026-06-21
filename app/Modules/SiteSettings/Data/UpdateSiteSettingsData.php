<?php

namespace App\Modules\SiteSettings\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class UpdateSiteSettingsData extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $footer
     */
    public function __construct(
        public int $updatedByUserId,
        public array $footer,
    ) {}
}
