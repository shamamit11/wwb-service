<?php

namespace App\Modules\SiteSettings\Repositories;

use App\Models\SiteSettings;
use App\Modules\SiteSettings\Data\UpdateSiteSettingsData;

interface SiteSettingsRepository
{
    public function getSingleton(): SiteSettings;

    public function update(SiteSettings $siteSettings, UpdateSiteSettingsData $data): SiteSettings;
}
