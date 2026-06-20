<?php

namespace App\Modules\AboutPage\Repositories;

use App\Models\AboutPage;
use App\Modules\AboutPage\Data\UpdateAboutPageData;

class EloquentAboutPageRepository implements AboutPageRepository
{
    public function getSingleton(): AboutPage
    {
        return AboutPage::query()
            ->firstOrCreate(
                ['singleton_key' => AboutPage::SINGLETON_KEY],
                array_merge(
                    ['updated_by_user_id' => null],
                    AboutPage::defaultPayload(),
                ),
            )
            ->loadMissing('updatedBy');
    }

    public function update(AboutPage $aboutPage, UpdateAboutPageData $data): AboutPage
    {
        $aboutPage->update([
            'hero' => $data->hero,
            'mission_section' => $data->missionSection,
            'stats_section' => $data->statsSection,
            'values_section' => $data->valuesSection,
            'team_section' => $data->teamSection,
            'seo' => $data->seo,
            'updated_by_user_id' => $data->updatedByUserId,
        ]);

        return $aboutPage->refresh()->load('updatedBy');
    }
}
