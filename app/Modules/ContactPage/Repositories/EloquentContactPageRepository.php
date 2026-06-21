<?php

namespace App\Modules\ContactPage\Repositories;

use App\Models\ContactPage;
use App\Modules\ContactPage\Data\UpdateContactPageData;

class EloquentContactPageRepository implements ContactPageRepository
{
    public function getSingleton(): ContactPage
    {
        return ContactPage::query()
            ->firstOrCreate(
                ['singleton_key' => ContactPage::SINGLETON_KEY],
                array_merge(
                    ['updated_by_user_id' => null],
                    ContactPage::defaultPayload(),
                ),
            )
            ->loadMissing('updatedBy');
    }

    public function update(ContactPage $contactPage, UpdateContactPageData $data): ContactPage
    {
        $contactPage->update([
            'hero' => $data->hero,
            'contact_form' => $data->contactForm,
            'contact_reasons' => $data->contactReasons,
            'seo' => $data->seo,
            'updated_by_user_id' => $data->updatedByUserId,
        ]);

        return $contactPage->refresh()->load('updatedBy');
    }
}
