<?php

namespace App\Http\Requests\Api\V1\Admin\Concerns;

use App\Enums\ContentBlockType;
use App\Modules\Posts\Data\PostBlockPayloadData;

trait InteractsWithPostData
{
    /**
     * @param  array<int, array{block_type:string,sort_order:int,content:array<string,mixed>,source_template_block_id?:int|null}>  $blocks
     * @return list<PostBlockPayloadData>
     */
    protected function mapBlocks(array $blocks): array
    {
        return array_map(
            fn (array $block): PostBlockPayloadData => new PostBlockPayloadData(
                blockType: $block['block_type'],
                sortOrder: $block['sort_order'],
                content: $this->normalizeBlockContent($block['block_type'], $block['content']),
                sourceTemplateBlockId: $block['source_template_block_id'] ?? null,
            ),
            $blocks,
        );
    }

    /**
     * @param  array<string, mixed>|array<int, mixed>  $content
     * @return array<string, mixed>
     */
    protected function normalizeBlockContent(string $blockType, array $content): array
    {
        if (! array_is_list($content)) {
            return $content;
        }

        $lines = array_values(array_filter(array_map(
            static fn (mixed $item): string => trim((string) $item),
            $content,
        ), static fn (string $line): bool => $line !== ''));

        return match ($blockType) {
            ContentBlockType::HEADING->value => [
                'text' => $lines[0] ?? '',
                'level' => 2,
            ],
            ContentBlockType::PARAGRAPH->value, ContentBlockType::CALLOUT->value => [
                'markdown' => implode("\n\n", $lines),
            ],
            ContentBlockType::IMAGE->value => [
                'url' => $lines[0] ?? null,
                'caption' => $lines[1] ?? null,
                'alt_text' => $lines[2] ?? null,
            ],
            ContentBlockType::QUOTE->value => [
                'quote_markdown' => $lines[0] ?? '',
                'attribution' => count($lines) > 1 ? implode("\n", array_slice($lines, 1)) : null,
            ],
            ContentBlockType::LIST->value => [
                'items' => $lines,
            ],
            ContentBlockType::CODE->value => [
                'code' => implode("\n", $lines),
            ],
            ContentBlockType::FAQ->value => [
                'items' => $this->normalizeFaqItems($lines),
            ],
            default => [
                'markdown' => implode("\n\n", $lines),
            ],
        };
    }

    /**
     * @param  list<string>  $lines
     * @return list<array{question:string,answer_markdown:string}>
     */
    protected function normalizeFaqItems(array $lines): array
    {
        $items = [];
        $pendingQuestion = null;

        foreach ($lines as $line) {
            if (str_starts_with(strtolower($line), 'question:')) {
                $pendingQuestion = trim(substr($line, 9));

                continue;
            }

            if (str_starts_with(strtolower($line), 'answer:')) {
                $answer = trim(substr($line, 7));

                if ($pendingQuestion !== null && $pendingQuestion !== '' && $answer !== '') {
                    $items[] = [
                        'question' => $pendingQuestion,
                        'answer_markdown' => $answer,
                    ];
                }

                $pendingQuestion = null;
            }
        }

        if ($items !== []) {
            return $items;
        }

        $pairs = array_chunk($lines, 2);

        return array_values(array_filter(array_map(
            static function (array $pair): ?array {
                $question = $pair[0] ?? '';
                $answer = $pair[1] ?? '';

                if ($question === '' || $answer === '') {
                    return null;
                }

                return [
                    'question' => $question,
                    'answer_markdown' => $answer,
                ];
            },
            $pairs,
        )));
    }
}
