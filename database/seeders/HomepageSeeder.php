<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Homepage;
use App\Models\Post;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class HomepageSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();

        $guidePostIds = Post::query()
            ->whereIn('slug', ['how-ai-agent-memory-works', 'laravel-queue-timeout-patterns'])
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        $categoryIds = Category::query()
            ->whereIn('slug', ['ai-tools', 'seo', 'developer-ai'])
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        Homepage::query()->updateOrCreate(
            ['singleton_key' => Homepage::SINGLETON_KEY],
            [
                ...Homepage::defaultPayload(),
                'singleton_key' => Homepage::SINGLETON_KEY,
                'hero' => [
                    'eyebrow' => 'Wide Web Blog',
                    'title' => 'Practical AI publishing systems for real editorial teams',
                    'description' => 'Guides, workflows, and implementation notes for AI-assisted blogging, SEO, and automation.',
                    'primary_cta_label' => 'Read the latest guide',
                    'primary_cta_url' => '/posts/how-ai-agent-memory-works',
                    'secondary_cta_label' => 'Browse AI topics',
                    'secondary_cta_url' => '/topics',
                    'media_url' => null,
                    'media_alt' => null,
                ],
                'featured_editorial' => [
                    'title' => 'Featured Editorial',
                    'description' => 'Recent deep dives and systems notes.',
                    'mode' => Homepage::SECTION_MODE_AUTOMATIC,
                    'post_ids' => [],
                    'category_ids' => null,
                    'limit' => 2,
                ],
                'guide_section' => [
                    'title' => 'Recent Articles',
                    'description' => 'Recent published articles from the editorial stream.',
                    'mode' => Homepage::SECTION_MODE_AUTOMATIC,
                    'post_ids' => [],
                    'category_ids' => null,
                    'limit' => 6,
                ],
                'topic_section' => [
                    'title' => 'Explore Core Topics',
                    'description' => 'Browse all active categories automatically.',
                    'category_ids' => [],
                ],
                'promo_section' => [
                    'enabled' => true,
                    'eyebrow' => 'Editorial Systems',
                    'title' => 'Build better AI-assisted publishing workflows',
                    'description' => 'Use grounded briefs, draft-only review, and clear approval states.',
                    'bullet_points' => ['Knowledge-grounded prompts', 'Draft-first workflows', 'Review-only refinement tools'],
                    'primary_cta_label' => 'See editorial workflow',
                    'primary_cta_url' => '/pages/editorial-workflow-guidelines',
                    'stats' => [
                        ['label' => 'Core AI workflows', 'value' => '6'],
                        ['label' => 'Publishing guardrails', 'value' => 'Draft-first'],
                    ],
                ],
                'newsletter_section' => [
                    'enabled' => true,
                    'title' => 'Get workflow notes in your inbox',
                    'description' => 'Subscribe for new AI publishing guides and editorial systems updates.',
                ],
                'seo' => [
                    'meta_title' => 'Wide Web Blog',
                    'meta_description' => 'Practical guides about AI publishing systems, SEO workflows, and content operations.',
                ],
                'updated_by_user_id' => $admin?->id,
            ],
        );
    }
}
