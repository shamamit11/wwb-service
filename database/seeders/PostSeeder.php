<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Post;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();
        $category = $this->firstCategory();

        if ($admin === null) {
            $this->warn('Skipping post seeding because no user record exists.');

            return;
        }

        if ($category === null) {
            $this->warn('Skipping post seeding because categories are excluded and no category records exist.');

            return;
        }

        $featuredMedia = Media::query()->where('object_key', 'seed/media/ai-agent-memory-cover.webp')->first();

        foreach ($this->records($admin->id, $category->id, $featuredMedia?->id) as $attributes) {
            Post::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(int $adminUserId, int $categoryId, ?int $featuredMediaId): array
    {
        return [
            [
                'author_user_id' => $adminUserId,
                'category_id' => $categoryId,
                'featured_media_id' => $featuredMediaId,
                'title' => 'How AI Agent Memory Works',
                'slug' => 'how-ai-agent-memory-works',
                'short_description' => 'A practical look at short-term and long-term memory patterns in AI agents.',
                'description' => 'An editorial overview of how memory shapes agent behavior.',
                'full_article_html' => '<h1>How AI Agent Memory Works</h1><p>Memory patterns determine how agents retain useful context over time.</p>',
                'full_article_delta' => null,
                'faq' => [],
                'status' => Post::STATUS_PUBLISHED,
                'visibility' => Post::VISIBILITY_PUBLIC,
                'published_at' => now()->subDay(),
                'meta' => [
                    'seeded' => true,
                    'seo' => ['title' => 'How AI Agent Memory Works'],
                ],
            ],
            [
                'author_user_id' => $adminUserId,
                'category_id' => $categoryId,
                'featured_media_id' => null,
                'title' => 'Laravel Queue Timeout Patterns',
                'slug' => 'laravel-queue-timeout-patterns',
                'short_description' => 'Editorial draft covering retries, timeouts, and idempotency.',
                'description' => 'A draft article about operational queue safety.',
                'full_article_html' => '<h1>Laravel Queue Timeout Patterns</h1><p>Retries happen, so idempotency matters.</p>',
                'full_article_delta' => null,
                'faq' => [],
                'status' => Post::STATUS_DRAFT,
                'visibility' => Post::VISIBILITY_INTERNAL,
                'published_at' => null,
                'meta' => [
                    'seeded' => true,
                    'workflow' => ['state' => 'draft'],
                ],
            ],
        ];
    }
}
