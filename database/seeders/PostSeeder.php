<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Post;
use App\Models\Template;
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

        $tutorialTemplate = Template::query()->where('slug', 'tutorial')->first();
        $featuredMedia = Media::query()->where('object_key', 'seed/media/ai-agent-memory-cover.webp')->first();

        foreach ($this->records($admin->id, $category->id, $tutorialTemplate?->id, $featuredMedia?->id) as $attributes) {
            Post::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(int $adminUserId, int $categoryId, ?int $templateId, ?int $featuredMediaId): array
    {
        return [
            [
                'author_user_id' => $adminUserId,
                'category_id' => $categoryId,
                'template_id' => $templateId,
                'featured_media_id' => $featuredMediaId,
                'title' => 'How AI Agent Memory Works',
                'slug' => 'how-ai-agent-memory-works',
                'excerpt' => 'A practical look at short-term and long-term memory patterns in AI agents.',
                'status' => Post::STATUS_PUBLISHED,
                'visibility' => Post::VISIBILITY_PUBLIC,
                'published_at' => now()->subDay(),
                'scheduled_for' => null,
                'content_version' => 1,
                'reading_time_minutes' => 8,
                'word_count' => 1200,
                'is_featured' => true,
                'meta' => [
                    'seeded' => true,
                    'seo' => ['title' => 'How AI Agent Memory Works'],
                ],
            ],
            [
                'author_user_id' => $adminUserId,
                'category_id' => $categoryId,
                'template_id' => $templateId,
                'featured_media_id' => null,
                'title' => 'Laravel Queue Timeout Patterns',
                'slug' => 'laravel-queue-timeout-patterns',
                'excerpt' => 'Editorial draft covering retries, timeouts, and idempotency.',
                'status' => Post::STATUS_DRAFT,
                'visibility' => Post::VISIBILITY_INTERNAL,
                'published_at' => null,
                'scheduled_for' => null,
                'content_version' => 1,
                'reading_time_minutes' => 6,
                'word_count' => 900,
                'is_featured' => false,
                'meta' => [
                    'seeded' => true,
                    'workflow' => ['state' => 'draft'],
                ],
            ],
        ];
    }
}
