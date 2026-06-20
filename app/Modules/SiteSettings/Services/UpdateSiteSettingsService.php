<?php

namespace App\Modules\SiteSettings\Services;

use App\Models\SiteSettings;
use App\Modules\SiteSettings\Data\UpdateSiteSettingsData;
use App\Modules\SiteSettings\Repositories\SiteSettingsRepository;
use App\Support\AuditActivityLogger;

class UpdateSiteSettingsService
{
    public function __construct(
        private readonly SiteSettingsRepository $siteSettings,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(UpdateSiteSettingsData $data): SiteSettings
    {
        $siteSettings = $this->siteSettings->getSingleton();

        $old = [
            'footer' => $siteSettings->footer,
        ];

        $updated = $this->siteSettings->update($siteSettings, $data);

        $this->audit->log(
            logName: 'content',
            description: 'site-settings.updated',
            event: 'updated',
            subject: $updated,
            attributes: [
                'footer' => $updated->footer,
            ],
            old: $old,
        );

        return $updated;
    }
}
