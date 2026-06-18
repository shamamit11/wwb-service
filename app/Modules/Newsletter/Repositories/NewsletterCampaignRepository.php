<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterCampaign;
use App\Modules\Newsletter\Data\CreateNewsletterCampaignData;
use App\Modules\Newsletter\Data\UpdateNewsletterCampaignData;
use Illuminate\Database\Eloquent\Collection;

interface NewsletterCampaignRepository
{
    public function create(CreateNewsletterCampaignData $data): NewsletterCampaign;

    public function update(NewsletterCampaign $campaign, UpdateNewsletterCampaignData $data): NewsletterCampaign;

    public function delete(NewsletterCampaign $campaign): void;

    public function findById(int $id): ?NewsletterCampaign;

    /**
     * @return Collection<int, NewsletterCampaign>
     */
    public function getAllOrdered(): Collection;
}
