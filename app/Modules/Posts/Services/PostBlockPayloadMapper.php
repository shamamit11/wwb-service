<?php

namespace App\Modules\Posts\Services;

use App\Enums\ContentBlockType;
use App\Modules\Posts\Data\CreatePostBlockData;
use App\Modules\Posts\Data\PostBlockPayloadData;
use App\Modules\Posts\Exceptions\InvalidPostBlockPayloadException;
use Illuminate\Support\Str;

class PostBlockPayloadMapper
{
    /**
     * @param  list<PostBlockPayloadData>  $blocks
     * @return list<CreatePostBlockData>
     */
    public function mapMany(array $blocks): array
    {
        return array_map(
            fn (PostBlockPayloadData $block): CreatePostBlockData => $this->map($block),
            $blocks,
        );
    }

    public function map(PostBlockPayloadData $block): CreatePostBlockData
    {
        $content = $block->content;

        return match ($block->blockType) {
            ContentBlockType::HEADING->value => $this->mapHeading($block, $content),
            ContentBlockType::PARAGRAPH->value => $this->mapParagraph($block, $content),
            ContentBlockType::IMAGE->value => $this->mapImage($block, $content),
            ContentBlockType::QUOTE->value => $this->mapQuote($block, $content),
            ContentBlockType::LIST->value => $this->mapList($block, $content),
            ContentBlockType::CODE->value => $this->mapCode($block, $content),
            ContentBlockType::FAQ->value => $this->mapFaq($block, $content),
            ContentBlockType::CALLOUT->value => $this->mapCallout($block, $content),
            default => throw new InvalidPostBlockPayloadException("Unsupported block type [{$block->blockType}]."),
        };
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function mapHeading(PostBlockPayloadData $block, array $content): CreatePostBlockData
    {
        $text = trim((string) ($content['text'] ?? ''));
        $level = max(1, min(6, (int) ($content['level'] ?? 2)));
        $markdown = $text !== '' ? str_repeat('#', $level).' '.$text : null;

        return $this->build(
            block: $block,
            contentMarkdown: $markdown,
            plainTextCache: $text !== '' ? $text : null,
            settings: ['level' => $level],
        );
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function mapParagraph(PostBlockPayloadData $block, array $content): CreatePostBlockData
    {
        $markdown = $this->nullableString($content['markdown'] ?? null);

        return $this->build(
            block: $block,
            contentMarkdown: $markdown,
            plainTextCache: $this->toPlainText($markdown),
            settings: [],
        );
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function mapImage(PostBlockPayloadData $block, array $content): CreatePostBlockData
    {
        $caption = $this->nullableString($content['caption'] ?? null);

        return $this->build(
            block: $block,
            contentMarkdown: $caption,
            plainTextCache: $this->toPlainText($caption ?? ($content['alt_text'] ?? null)),
            settings: $content,
        );
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function mapQuote(PostBlockPayloadData $block, array $content): CreatePostBlockData
    {
        $markdown = $this->nullableString($content['quote_markdown'] ?? null);

        return $this->build(
            block: $block,
            contentMarkdown: $markdown,
            plainTextCache: $this->toPlainText($markdown),
            settings: [
                'attribution' => $this->nullableString($content['attribution'] ?? null),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function mapList(PostBlockPayloadData $block, array $content): CreatePostBlockData
    {
        $items = array_values(array_filter(array_map(
            fn (mixed $item): string => trim((string) $item),
            is_array($content['items'] ?? null) ? $content['items'] : [],
        ), static fn (string $item): bool => $item !== ''));
        $markdown = $items === [] ? null : collect($items)->map(fn (string $item): string => "- {$item}")->implode("\n");

        return $this->build(
            block: $block,
            contentMarkdown: $markdown,
            plainTextCache: $items === [] ? null : implode("\n", $items),
            settings: [
                'items' => $items,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function mapCode(PostBlockPayloadData $block, array $content): CreatePostBlockData
    {
        $code = $this->nullableString($content['code'] ?? null);

        return $this->build(
            block: $block,
            contentMarkdown: $code,
            plainTextCache: $code,
            settings: [
                'language' => $this->nullableString($content['language'] ?? null),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function mapFaq(PostBlockPayloadData $block, array $content): CreatePostBlockData
    {
        $items = is_array($content['items'] ?? null) ? array_values($content['items']) : [];
        $markdownParts = [];
        $plainTextParts = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer_markdown'] ?? ''));

            if ($question === '' && $answer === '') {
                continue;
            }

            $markdownParts[] = "## {$question}\n{$answer}";
            $plainTextParts[] = trim("{$question}\n".$this->toPlainText($answer));
        }

        return $this->build(
            block: $block,
            contentMarkdown: $markdownParts === [] ? null : implode("\n\n", $markdownParts),
            plainTextCache: $plainTextParts === [] ? null : implode("\n\n", $plainTextParts),
            settings: [
                'items' => array_values(array_filter($items, 'is_array')),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function mapCallout(PostBlockPayloadData $block, array $content): CreatePostBlockData
    {
        $markdown = $this->nullableString($content['markdown'] ?? null);

        return $this->build(
            block: $block,
            contentMarkdown: $markdown,
            plainTextCache: $this->toPlainText($markdown),
            settings: [
                'variant' => $this->nullableString($content['variant'] ?? null),
            ],
        );
    }

    /**
     * @param  array<string, mixed>|null  $settings
     */
    private function build(
        PostBlockPayloadData $block,
        ?string $contentMarkdown,
        ?string $plainTextCache,
        ?array $settings,
    ): CreatePostBlockData {
        return new CreatePostBlockData(
            blockType: $block->blockType,
            sortOrder: $block->sortOrder,
            contentMarkdown: $contentMarkdown,
            contentHtmlCache: null,
            plainTextCache: $plainTextCache,
            settings: $settings,
            sourceTemplateBlockId: $block->sourceTemplateBlockId,
        );
    }

    private function nullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function toPlainText(mixed $value): ?string
    {
        $string = $this->nullableString($value);

        if ($string === null) {
            return null;
        }

        $plain = trim((string) Str::of($string)
            ->replaceMatches('/[`*_>#-]+/', ' ')
            ->replaceMatches('/\s+/', ' '));

        return $plain !== '' ? $plain : null;
    }
}
