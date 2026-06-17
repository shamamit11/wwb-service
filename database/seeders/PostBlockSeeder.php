<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostBlock;
use App\Models\TemplateBlock;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class PostBlockSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $posts = Post::query()
            ->whereIn('slug', ['how-ai-agent-memory-works', 'laravel-queue-timeout-patterns'])
            ->get()
            ->keyBy('slug');

        if ($posts->isEmpty()) {
            $this->warn('Skipping post block seeding because the base posts are missing.');

            return;
        }

        foreach ($this->records() as $postSlug => $blocks) {
            $post = $posts->get($postSlug);

            if ($post === null) {
                continue;
            }

            foreach ($blocks as $attributes) {
                $sourceTemplateBlockId = null;

                if ($post->template_id !== null) {
                    $sourceTemplateBlockId = TemplateBlock::query()
                        ->where('template_id', $post->template_id)
                        ->where('sort_order', $attributes['sort_order'])
                        ->value('id');
                }

                PostBlock::query()->updateOrCreate(
                    [
                        'post_id' => $post->id,
                        'sort_order' => $attributes['sort_order'],
                    ],
                    [
                        ...$attributes,
                        'post_id' => $post->id,
                        'source_template_block_id' => $sourceTemplateBlockId,
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
            'how-ai-agent-memory-works' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'content_markdown' => '# How AI Agent Memory Works',
                    'content_html_cache' => '<h1>How AI Agent Memory Works</h1>',
                    'plain_text_cache' => 'How AI Agent Memory Works',
                    'settings' => ['level' => 1],
                ],
                [
                    'block_type' => 'paragraph',
                    'sort_order' => 2,
                    'content_markdown' => 'Memory patterns determine how agents retain useful context over time.',
                    'content_html_cache' => '<p>Memory patterns determine how agents retain useful context over time.</p>',
                    'plain_text_cache' => 'Memory patterns determine how agents retain useful context over time.',
                    'settings' => null,
                ],
            ],
            'laravel-queue-timeout-patterns' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'content_markdown' => '# Laravel Queue Timeout Patterns',
                    'content_html_cache' => '<h1>Laravel Queue Timeout Patterns</h1>',
                    'plain_text_cache' => 'Laravel Queue Timeout Patterns',
                    'settings' => ['level' => 1],
                ],
                [
                    'block_type' => 'faq',
                    'sort_order' => 2,
                    'content_markdown' => "- **What matters most?** Idempotency.\n- **Why?** Retries happen.",
                    'content_html_cache' => '<ul><li><strong>What matters most?</strong> Idempotency.</li><li><strong>Why?</strong> Retries happen.</li></ul>',
                    'plain_text_cache' => 'What matters most? Idempotency. Why? Retries happen.',
                    'settings' => [
                        'items' => [
                            [
                                'question' => 'What matters most?',
                                'answer_markdown' => 'Idempotency.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
