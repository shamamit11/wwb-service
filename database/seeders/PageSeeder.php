<?php

namespace Database\Seeders;

use App\Models\Page;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();

        if ($admin === null) {
            $this->warn('Skipping page seeding because no user record exists.');

            return;
        }

        foreach ($this->records($admin->id) as $attributes) {
            Page::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(int $adminUserId): array
    {
        return [
            [
                'created_by_user_id' => $adminUserId,
                'updated_by_user_id' => $adminUserId,
                'title' => 'About Wide Web Blog',
                'slug' => 'about-wide-web-blog',
                'type' => Page::TYPE_MARKETING,
                'status' => Page::STATUS_PUBLISHED,
                'summary' => 'What Wide Web Blog covers and how the editorial process works.',
                'content_markdown' => "# About Wide Web Blog\n\nWe publish practical guides about AI tools, SEO systems, and content workflows.",
                'visibility' => Page::VISIBILITY_PUBLIC,
                'published_at' => now()->subDays(5),
                'scheduled_for' => null,
                'meta' => [
                    'seeded' => true,
                    'hero' => ['cta_label' => 'Explore latest posts'],
                ],
            ],
            [
                'created_by_user_id' => $adminUserId,
                'updated_by_user_id' => $adminUserId,
                'title' => 'Editorial Workflow Guidelines',
                'slug' => 'editorial-workflow-guidelines',
                'type' => Page::TYPE_SUPPORT,
                'status' => Page::STATUS_PUBLISHED,
                'summary' => 'Internal notes for draft review, AI use, and publication guardrails.',
                'content_markdown' => "# Editorial Workflow Guidelines\n\nAI-generated content remains draft-only until an editor approves it.",
                'visibility' => Page::VISIBILITY_INTERNAL,
                'published_at' => now()->subDays(2),
                'scheduled_for' => null,
                'meta' => [
                    'seeded' => true,
                    'audience' => 'editorial-team',
                ],
            ],
        ];
    }
}
