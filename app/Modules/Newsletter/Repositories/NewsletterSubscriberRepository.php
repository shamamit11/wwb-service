<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\CreateNewsletterSubscriberData;
use App\Modules\Newsletter\Data\UpdateNewsletterSubscriberData;
use Illuminate\Database\Eloquent\Collection;

interface NewsletterSubscriberRepository
{
    public function create(CreateNewsletterSubscriberData $data): NewsletterSubscriber;

    public function update(NewsletterSubscriber $subscriber, UpdateNewsletterSubscriberData $data): NewsletterSubscriber;

    public function findById(int $id): ?NewsletterSubscriber;

    public function findByEmail(string $email): ?NewsletterSubscriber;

    public function findByUnsubscribeToken(string $unsubscribeToken): ?NewsletterSubscriber;

    public function save(NewsletterSubscriber $subscriber): NewsletterSubscriber;

    /**
     * @return Collection<int, NewsletterSubscriber>
     */
    public function getAllOrdered(): Collection;

    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, NewsletterSubscriber>
     */
    public function findActiveByIds(array $ids): Collection;

    /**
     * @param  array<int, int>  $listIds
     * @return Collection<int, NewsletterSubscriber>
     */
    public function findActiveByListIds(array $listIds): Collection;

    /**
     * @return Collection<int, NewsletterSubscriber>
     */
    public function findAllActive(): Collection;
}
