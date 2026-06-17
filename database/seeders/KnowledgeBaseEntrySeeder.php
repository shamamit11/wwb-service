<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseEntry;
use App\Models\Media;
use App\Models\Post;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class KnowledgeBaseEntrySeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();

        if ($admin === null) {
            $this->warn('Skipping knowledge base seeding because no user record exists.');

            return;
        }

        $featuredMediaId = Media::query()
            ->where('object_key', 'seed/media/laravel-queues-reference.webp')
            ->value('id');

        $linkedPost = Post::query()->where('slug', 'laravel-queue-timeout-patterns')->first();

        foreach ($this->records($admin->id, $featuredMediaId, $linkedPost) as $attributes) {
            KnowledgeBaseEntry::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(int $adminUserId, ?int $featuredMediaId, ?Post $linkedPost): array
    {
        return [
            [
                'created_by_user_id' => $adminUserId,
                'updated_by_user_id' => $adminUserId,
                'title' => 'Laravel Queue Retry Patterns',
                'slug' => 'laravel-queue-retry-patterns',
                'entry_type' => KnowledgeBaseEntry::TYPE_ARCHITECTURE,
                'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
                'summary' => 'Notes on retries, timeouts, and idempotent job design.',
                'content_markdown' => 'When queue-backed AI jobs retry, write paths must stay idempotent.',
                'source_url' => 'https://laravel.com/docs/queues',
                'featured_media_id' => $featuredMediaId,
                'metadata' => [
                    'tags' => ['laravel', 'queues', 'retries'],
                    KnowledgeBaseEntry::LINK_HOOKS_KEY => [
                        'posts' => $linkedPost === null ? [] : [[
                            'id' => $linkedPost->id,
                            'title' => $linkedPost->title,
                            'slug' => $linkedPost->slug,
                        ]],
                        'topics' => [['id' => 42]],
                    ],
                ],
            ],
            [
                'created_by_user_id' => $adminUserId,
                'updated_by_user_id' => $adminUserId,
                'title' => 'Topic Ideas',
                'slug' => 'topic-ideas',
                'entry_type' => KnowledgeBaseEntry::TYPE_IDEA,
                'status' => KnowledgeBaseEntry::STATUS_DRAFT,
                'summary' => 'Internal editorial topic list.',
                'content_markdown' => 'Ideas for future posts about AI agents, SEO systems, and tooling.',
                'source_url' => null,
                'featured_media_id' => null,
                'metadata' => [
                    'editorial_state' => 'backlog',
                ],
            ],
        ];
    }
}
