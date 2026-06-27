<?php

namespace Database\Seeders;

use App\Models\NewsletterList;
use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use Illuminate\Database\Seeder;

class NewsletterSubscriberSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->records() as $record) {
            $subscriber = NewsletterSubscriber::query()->updateOrCreate(
                ['email' => $record['email']],
                [
                    'name' => $record['name'],
                    'status' => $record['status'],
                    'source' => $record['source'],
                    'subscribed_at' => $record['subscribed_at'],
                    'unsubscribed_at' => $record['unsubscribed_at'],
                    'unsubscribe_token' => $record['unsubscribe_token'],
                    'metadata' => $record['metadata'],
                ],
            );

            $lists = $record['lists'];
            unset($record['lists']);

            $syncPayload = [];

            foreach ($lists as $listId => $pivot) {
                $syncPayload[$listId] = $pivot;
            }

            $subscriber->lists()->syncWithoutDetaching($syncPayload);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(): array
    {
        $weeklyListId = NewsletterList::query()->where('slug', 'weekly-editorial-systems')->value('id');
        $productListId = NewsletterList::query()->where('slug', 'product-updates')->value('id');

        return [
            [
                'email' => 'reader.one@example.com',
                'name' => 'Reader One',
                'status' => NewsletterSubscriberStatus::Active,
                'source' => 'seed',
                'subscribed_at' => now()->subDays(10),
                'unsubscribed_at' => null,
                'unsubscribe_token' => 'seed-unsub-reader-one',
                'metadata' => ['seeded' => true, 'interests' => ['ai_workflows', 'seo']],
                'lists' => array_filter([
                    $weeklyListId => [
                        'subscribed_at' => now()->subDays(10),
                        'unsubscribed_at' => null,
                    ],
                ], static fn (mixed $value): bool => $value !== null),
            ],
            [
                'email' => 'reader.two@example.com',
                'name' => 'Reader Two',
                'status' => NewsletterSubscriberStatus::Active,
                'source' => 'seed',
                'subscribed_at' => now()->subDays(6),
                'unsubscribed_at' => null,
                'unsubscribe_token' => 'seed-unsub-reader-two',
                'metadata' => ['seeded' => true, 'interests' => ['product_updates']],
                'lists' => array_filter([
                    $weeklyListId => [
                        'subscribed_at' => now()->subDays(6),
                        'unsubscribed_at' => null,
                    ],
                    $productListId => [
                        'subscribed_at' => now()->subDays(6),
                        'unsubscribed_at' => null,
                    ],
                ], static fn (mixed $value): bool => $value !== null),
            ],
        ];
    }
}
