<?php

namespace Database\Seeders;

use App\Models\Template;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();

        if ($admin === null) {
            $this->warn('Skipping template seeding because no user record exists.');

            return;
        }

        foreach ($this->records($admin->id) as $attributes) {
            Template::query()->updateOrCreate(
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
                'name' => 'Tutorial',
                'slug' => 'tutorial',
                'template_type' => Template::TYPE_TUTORIAL,
                'description' => 'Structured tutorial layout for editorial guides.',
                'status' => Template::STATUS_ACTIVE,
                'default_excerpt_prompt' => 'Summarize the steps, outcomes, and practical takeaways.',
                'default_meta' => [
                    'recommended_sections' => ['introduction', 'steps', 'faq'],
                    'seo_rules' => ['preferred_schema' => 'Article'],
                ],
            ],
            [
                'created_by_user_id' => $adminUserId,
                'updated_by_user_id' => $adminUserId,
                'name' => 'Comparison',
                'slug' => 'comparison',
                'template_type' => Template::TYPE_COMPARISON,
                'description' => 'Template for tradeoff-driven product and architecture comparisons.',
                'status' => Template::STATUS_DRAFT,
                'default_excerpt_prompt' => 'Capture the tradeoffs, recommendation, and decision context.',
                'default_meta' => [
                    'recommended_sections' => ['problem', 'option-a', 'option-b', 'recommendation'],
                ],
            ],
        ];
    }
}
