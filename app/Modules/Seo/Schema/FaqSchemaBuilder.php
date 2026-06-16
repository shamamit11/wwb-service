<?php

namespace App\Modules\Seo\Schema;

use App\Enums\ContentBlockType;
use App\Models\Post;
use App\Modules\Seo\Services\CanonicalUrlService;

class FaqSchemaBuilder
{
    public function __construct(
        private readonly CanonicalUrlService $canonicalUrls,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function build(Post $post): ?array
    {
        $entities = [];

        foreach ($post->blocks as $block) {
            if ($block->block_type !== ContentBlockType::FAQ->value) {
                continue;
            }

            $items = is_array($block->settings['items'] ?? null) ? $block->settings['items'] : [];

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $question = trim((string) ($item['question'] ?? ''));
                $answer = trim((string) ($item['answer_markdown'] ?? ''));

                if ($question === '' || $answer === '') {
                    continue;
                }

                $entities[] = [
                    '@type' => 'Question',
                    'name' => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $answer,
                    ],
                ];
            }
        }

        if ($entities === []) {
            return null;
        }

        $canonical = $this->canonicalUrls->for($post) ?? rtrim((string) config('app.url'), '/').'/';

        return [
            '@type' => 'FAQPage',
            '@id' => "{$canonical}#faq",
            'mainEntity' => $entities,
        ];
    }
}
