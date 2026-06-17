<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\TemplateBlock;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class TemplateBlockSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $templates = Template::query()
            ->whereIn('slug', ['tutorial', 'comparison'])
            ->get()
            ->keyBy('slug');

        if ($templates->isEmpty()) {
            $this->warn('Skipping template block seeding because the base templates are missing.');

            return;
        }

        foreach ($this->records() as $slug => $blocks) {
            $template = $templates->get($slug);

            if ($template === null) {
                continue;
            }

            foreach ($blocks as $attributes) {
                TemplateBlock::query()->updateOrCreate(
                    [
                        'template_id' => $template->id,
                        'sort_order' => $attributes['sort_order'],
                    ],
                    [
                        ...$attributes,
                        'template_id' => $template->id,
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function records(): array
    {
        return [
            'tutorial' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'label' => 'Title',
                    'default_markdown' => '# {{title}}',
                    'settings' => ['level' => 1],
                    'is_required' => true,
                ],
                [
                    'block_type' => 'paragraph',
                    'sort_order' => 2,
                    'label' => 'Introduction',
                    'default_markdown' => 'Introduce {{topic}} with practical context.',
                    'settings' => null,
                    'is_required' => true,
                ],
            ],
            'comparison' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'label' => 'Title',
                    'default_markdown' => '# {{title}}',
                    'settings' => ['level' => 1],
                    'is_required' => true,
                ],
                [
                    'block_type' => 'callout',
                    'sort_order' => 2,
                    'label' => 'Decision Summary',
                    'default_markdown' => 'Summarize the recommendation and why it wins.',
                    'settings' => ['variant' => 'info'],
                    'is_required' => false,
                ],
            ],
        ];
    }
}
