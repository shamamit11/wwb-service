<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterSubscriber;

class NewsletterSubscriberListManager
{
    /**
     * @param  array<int, int>  $listIds
     */
    public function subscribeToLists(NewsletterSubscriber $subscriber, array $listIds): NewsletterSubscriber
    {
        if ($listIds === []) {
            return $subscriber->refresh()->load('lists');
        }

        $attributes = [];

        foreach ($listIds as $listId) {
            $attributes[$listId] = [
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'updated_at' => now(),
            ];
        }

        $subscriber->lists()->syncWithoutDetaching($attributes);

        return $subscriber->refresh()->load('lists');
    }

    public function unsubscribeFromAllLists(NewsletterSubscriber $subscriber): NewsletterSubscriber
    {
        foreach ($subscriber->lists()->pluck('newsletter_lists.id')->all() as $listId) {
            $subscriber->lists()->updateExistingPivot($listId, [
                'unsubscribed_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $subscriber->refresh()->load('lists');
    }
}
