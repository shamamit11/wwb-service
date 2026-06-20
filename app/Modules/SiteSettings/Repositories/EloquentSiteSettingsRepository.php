<?php

namespace App\Modules\SiteSettings\Repositories;

use App\Models\SiteSettings;
use App\Modules\SiteSettings\Data\UpdateSiteSettingsData;

class EloquentSiteSettingsRepository implements SiteSettingsRepository
{
    public function getSingleton(): SiteSettings
    {
        return SiteSettings::query()
            ->firstOrCreate(
                ['singleton_key' => SiteSettings::SINGLETON_KEY],
                array_merge(
                    ['updated_by_user_id' => null],
                    SiteSettings::defaultPayload(),
                ),
            )
            ->loadMissing('updatedBy');
    }

    public function update(SiteSettings $siteSettings, UpdateSiteSettingsData $data): SiteSettings
    {
        $siteSettings->update([
            'footer' => $data->footer,
            'updated_by_user_id' => $data->updatedByUserId,
        ]);

        return $siteSettings->refresh()->load('updatedBy');
    }
}
