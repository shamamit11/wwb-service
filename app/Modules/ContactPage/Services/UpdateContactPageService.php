<?php

namespace App\Modules\ContactPage\Services;

use App\Models\ContactPage;
use App\Modules\ContactPage\Data\UpdateContactPageData;
use App\Modules\ContactPage\Repositories\ContactPageRepository;
use App\Support\AuditActivityLogger;

class UpdateContactPageService
{
    public function __construct(
        private readonly ContactPageRepository $contactPages,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(UpdateContactPageData $data): ContactPage
    {
        $contactPage = $this->contactPages->getSingleton();

        $old = [
            'hero' => $contactPage->hero,
            'contact_form' => $contactPage->contact_form,
            'contact_reasons' => $contactPage->contact_reasons,
            'seo' => $contactPage->seo,
        ];

        $updated = $this->contactPages->update($contactPage, $data);

        $this->audit->log(
            logName: 'content',
            description: 'contact-page.updated',
            event: 'updated',
            subject: $updated,
            attributes: [
                'hero' => $updated->hero,
                'contact_form' => $updated->contact_form,
                'contact_reasons' => $updated->contact_reasons,
                'seo' => $updated->seo,
            ],
            old: $old,
        );

        return $updated;
    }
}
