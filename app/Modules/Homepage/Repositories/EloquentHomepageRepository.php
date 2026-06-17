<?php

namespace App\Modules\Homepage\Repositories;

use App\Models\Homepage;
use App\Modules\Homepage\Data\UpdateHomepageData;

class EloquentHomepageRepository implements HomepageRepository
{
    public function getSingleton(): Homepage
    {
        return Homepage::query()
            ->firstOrCreate(
                ['singleton_key' => Homepage::SINGLETON_KEY],
                array_merge(
                    ['updated_by_user_id' => null],
                    Homepage::defaultPayload(),
                ),
            )
            ->loadMissing('updatedBy');
    }

    public function update(Homepage $homepage, UpdateHomepageData $data): Homepage
    {
        $homepage->update([
            'hero' => $data->hero,
            'featured_editorial' => $data->featuredEditorial,
            'guide_section' => $data->guideSection,
            'topic_section' => $data->topicSection,
            'promo_section' => $data->promoSection,
            'newsletter_section' => $data->newsletterSection,
            'seo' => $data->seo,
            'updated_by_user_id' => $data->updatedByUserId,
        ]);

        return $homepage->refresh()->load('updatedBy');
    }
}
