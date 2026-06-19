<?php

namespace App\Modules\Newsletter\Repositories;

use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Data\CreateNewsletterSubscriberData;
use App\Modules\Newsletter\Data\UpdateNewsletterSubscriberData;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use Illuminate\Database\Eloquent\Collection;

class EloquentNewsletterSubscriberRepository implements NewsletterSubscriberRepository
{
    public function create(CreateNewsletterSubscriberData $data): NewsletterSubscriber
    {
        return NewsletterSubscriber::query()->create([
            'email' => $data->email,
            'name' => $data->name,
            'status' => $data->status,
            'source' => $data->source,
            'subscribed_at' => $data->subscribedAt,
            'unsubscribed_at' => $data->unsubscribedAt,
            'unsubscribe_token' => $data->unsubscribeToken,
            'metadata' => $data->metadata,
        ])->refresh();
    }

    public function update(NewsletterSubscriber $subscriber, UpdateNewsletterSubscriberData $data): NewsletterSubscriber
    {
        $subscriber->update([
            'email' => $data->email,
            'name' => $data->name,
            'status' => $data->status,
            'source' => $data->source,
            'subscribed_at' => $data->subscribedAt,
            'unsubscribed_at' => $data->unsubscribedAt,
            'metadata' => $data->metadata,
        ]);

        return $subscriber->refresh();
    }

    public function findById(int $id): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::query()->with('lists')->find($id);
    }

    public function findByEmail(string $email): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::query()
            ->with('lists')
            ->where('email', $email)
            ->first();
    }

    public function findByUnsubscribeToken(string $unsubscribeToken): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::query()
            ->with('lists')
            ->where('unsubscribe_token', $unsubscribeToken)
            ->first();
    }

    public function save(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        $subscriber->save();

        return $subscriber->refresh()->load('lists');
    }

    /**
     * @return Collection<int, NewsletterSubscriber>
     */
    public function getAllOrdered(): Collection
    {
        return NewsletterSubscriber::query()
            ->with('lists')
            ->orderBy('email')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, NewsletterSubscriber>
     */
    public function findActiveByIds(array $ids): Collection
    {
        return NewsletterSubscriber::query()
            ->with('lists')
            ->whereIn('id', $ids)
            ->where('status', NewsletterSubscriberStatus::Active->value)
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<int, int>  $listIds
     * @return Collection<int, NewsletterSubscriber>
     */
    public function findActiveByListIds(array $listIds): Collection
    {
        return NewsletterSubscriber::query()
            ->with('lists')
            ->where('status', NewsletterSubscriberStatus::Active->value)
            ->whereHas('lists', fn ($query) => $query->whereIn('newsletter_lists.id', $listIds))
            ->orderBy('newsletter_subscribers.id')
            ->get();
    }

    /**
     * @return Collection<int, NewsletterSubscriber>
     */
    public function findAllActive(): Collection
    {
        return NewsletterSubscriber::query()
            ->with('lists')
            ->where('status', NewsletterSubscriberStatus::Active->value)
            ->orderBy('email')
            ->orderBy('id')
            ->get();
    }
}
