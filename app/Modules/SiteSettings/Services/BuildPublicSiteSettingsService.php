<?php

namespace App\Modules\SiteSettings\Services;

use App\Models\SiteSettings;
use App\Modules\SiteSettings\Repositories\SiteSettingsRepository;

class BuildPublicSiteSettingsService
{
    public function __construct(
        private readonly SiteSettingsRepository $siteSettings,
    ) {}

    public function handle(): SiteSettings
    {
        return $this->siteSettings->getSingleton();
    }
}
