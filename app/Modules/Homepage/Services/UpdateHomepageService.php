<?php

namespace App\Modules\Homepage\Services;

use App\Models\Homepage;
use App\Modules\Homepage\Data\UpdateHomepageData;
use App\Modules\Homepage\Repositories\HomepageRepository;
use App\Support\AuditActivityLogger;

class UpdateHomepageService
{
    public function __construct(
        private readonly HomepageRepository $homepages,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(UpdateHomepageData $data): Homepage
    {
        $homepage = $this->homepages->getSingleton();

        $old = [
            'hero' => $homepage->hero,
            'featured_editorial' => $homepage->featured_editorial,
            'guide_section' => $homepage->guide_section,
            'topic_section' => $homepage->topic_section,
            'promo_section' => $homepage->promo_section,
            'newsletter_section' => $homepage->newsletter_section,
            'seo' => $homepage->seo,
        ];

        $updated = $this->homepages->update($homepage, $data);

        $this->audit->log(
            logName: 'content',
            description: 'homepage.updated',
            event: 'updated',
            subject: $updated,
            attributes: [
                'hero' => $updated->hero,
                'featured_editorial' => $updated->featured_editorial,
                'guide_section' => $updated->guide_section,
                'topic_section' => $updated->topic_section,
                'promo_section' => $updated->promo_section,
                'newsletter_section' => $updated->newsletter_section,
                'seo' => $updated->seo,
            ],
            old: $old,
        );

        return $updated;
    }
}
