<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseEntry;
use App\Models\Media;
use App\Models\Post;
use App\Models\SeoMetadata;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class SeoMetadataSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $ogImageMediaId = Media::query()
            ->where('object_key', 'seed/media/ai-agent-memory-cover.webp')
            ->value('id');

        $this->seedPostMetadata($ogImageMediaId);
        $this->seedKnowledgeBaseMetadata($ogImageMediaId);
    }

    private function seedPostMetadata(?int $ogImageMediaId): void
    {
        $post = Post::query()->where('slug', 'how-ai-agent-memory-works')->first();

        if ($post === null) {
            $this->warn('Skipping post SEO metadata seeding because the base post is missing.');

            return;
        }

        SeoMetadata::query()->updateOrCreate(
            [
                'seoable_type' => Post::class,
                'seoable_id' => $post->id,
            ],
            [
                'meta_title' => 'How AI Agent Memory Works',
                'meta_description' => 'A seeded walkthrough of short-term and long-term memory patterns in AI agents.',
                'canonical_url' => 'https://widewebblog.test/posts/how-ai-agent-memory-works',
                'robots_index' => true,
                'robots_follow' => true,
                'og_title' => 'How AI Agent Memory Works',
                'og_description' => 'A practical walkthrough of AI agent memory design.',
                'og_image_media_id' => $ogImageMediaId,
                'schema_type' => 'Article',
                'schema_payload' => [
                    'headline' => $post->title,
                    'type' => 'Article',
                ],
                'focus_keyword' => 'ai agent memory',
            ],
        );
    }

    private function seedKnowledgeBaseMetadata(?int $ogImageMediaId): void
    {
        $entry = KnowledgeBaseEntry::query()->where('slug', 'laravel-queue-retry-patterns')->first();

        if ($entry === null) {
            $this->warn('Skipping knowledge base SEO metadata seeding because the base entry is missing.');

            return;
        }

        SeoMetadata::query()->updateOrCreate(
            [
                'seoable_type' => KnowledgeBaseEntry::class,
                'seoable_id' => $entry->id,
            ],
            [
                'meta_title' => 'Laravel Queue Retry Patterns',
                'meta_description' => 'Seeded reference notes on retries, timeouts, and idempotency in Laravel queues.',
                'canonical_url' => 'https://widewebblog.test/knowledge-base/laravel-queue-retry-patterns',
                'robots_index' => false,
                'robots_follow' => true,
                'og_title' => 'Laravel Queue Retry Patterns',
                'og_description' => 'Reference notes for queue-backed content pipelines.',
                'og_image_media_id' => $ogImageMediaId,
                'schema_type' => 'TechArticle',
                'schema_payload' => [
                    'headline' => $entry->title,
                    'type' => 'TechArticle',
                ],
                'focus_keyword' => 'laravel queue retries',
            ],
        );
    }
}
