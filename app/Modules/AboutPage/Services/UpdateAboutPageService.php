<?php

namespace App\Modules\AboutPage\Services;

use App\Models\AboutPage;
use App\Modules\AboutPage\Data\UpdateAboutPageData;
use App\Modules\AboutPage\Repositories\AboutPageRepository;
use App\Support\AuditActivityLogger;

class UpdateAboutPageService
{
    public function __construct(
        private readonly AboutPageRepository $aboutPages,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(UpdateAboutPageData $data): AboutPage
    {
        $aboutPage = $this->aboutPages->getSingleton();

        $old = [
            'hero' => $aboutPage->hero,
            'mission_section' => $aboutPage->mission_section,
            'stats_section' => $aboutPage->stats_section,
            'values_section' => $aboutPage->values_section,
            'team_section' => $aboutPage->team_section,
            'seo' => $aboutPage->seo,
        ];

        $updated = $this->aboutPages->update($aboutPage, $data);

        $this->audit->log(
            logName: 'content',
            description: 'about-page.updated',
            event: 'updated',
            subject: $updated,
            attributes: [
                'hero' => $updated->hero,
                'mission_section' => $updated->mission_section,
                'stats_section' => $updated->stats_section,
                'values_section' => $updated->values_section,
                'team_section' => $updated->team_section,
                'seo' => $updated->seo,
            ],
            old: $old,
        );

        return $updated;
    }
}
