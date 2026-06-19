<?php

namespace App\Modules\Posts\Services;

use App\Enums\ContentBlockType;
use App\Modules\Posts\Data\PostBlockPayloadData;
use App\Modules\Posts\Exceptions\InvalidPostBlockPayloadException;

class PostBlockPayloadValidator
{
    /**
     * @param  list<PostBlockPayloadData>  $blocks
     */
    public function validate(array $blocks): void
    {
        if ($blocks === []) {
            throw new InvalidPostBlockPayloadException('At least one post block is required.');
        }

        $sortOrders = [];

        foreach ($blocks as $index => $block) {
            if (! in_array($block->blockType, ContentBlockType::values(), true)) {
                throw new InvalidPostBlockPayloadException("Unsupported block type [{$block->blockType}] at index {$index}.");
            }

            if (in_array($block->sortOrder, $sortOrders, true)) {
                throw new InvalidPostBlockPayloadException("Duplicate block sort_order [{$block->sortOrder}] is not allowed.");
            }

            $sortOrders[] = $block->sortOrder;
            $this->validateContent($block, $index);
        }
    }

    private function validateContent(PostBlockPayloadData $block, int $index): void
    {
        $content = $block->content;

        match ($block->blockType) {
            ContentBlockType::HEADING->value => $this->requireNonEmptyString($content['text'] ?? null, "Heading block text is required at index {$index}."),
            ContentBlockType::PARAGRAPH->value, ContentBlockType::CALLOUT->value => $this->requireNonEmptyString(
                $this->resolveMarkdownLikeContent($content),
                "Markdown content is required at index {$index}.",
            ),
            ContentBlockType::IMAGE->value => $this->requireAnyNonEmptyString([
                $content['url'] ?? null,
                $content['caption'] ?? null,
                $content['alt_text'] ?? null,
            ], "Image block content is required at index {$index}."),
            ContentBlockType::QUOTE->value => $this->requireNonEmptyString(
                $this->resolveQuoteContent($content),
                "Quote markdown is required at index {$index}.",
            ),
            ContentBlockType::LIST->value => $this->requireNonEmptyArray($content['items'] ?? null, "List items are required at index {$index}."),
            ContentBlockType::CODE->value => $this->requireNonEmptyString($this->resolveCodeContent($content), "Code content is required at index {$index}."),
            ContentBlockType::FAQ->value => $this->requireNonEmptyArray($content['items'] ?? null, "FAQ items are required at index {$index}."),
            default => throw new InvalidPostBlockPayloadException("Unsupported block type [{$block->blockType}] at index {$index}."),
        };
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function resolveMarkdownLikeContent(array $content): mixed
    {
        return $content['markdown']
            ?? $content['text']
            ?? $content['content']
            ?? $content['content_markdown']
            ?? null;
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function resolveQuoteContent(array $content): mixed
    {
        return $content['quote_markdown']
            ?? $this->resolveMarkdownLikeContent($content);
    }

    /**
     * @param  array<string, mixed>  $content
     */
    private function resolveCodeContent(array $content): mixed
    {
        return $content['code']
            ?? $this->resolveMarkdownLikeContent($content);
    }

    private function requireNonEmptyString(mixed $value, string $message): void
    {
        if (trim((string) $value) === '') {
            throw new InvalidPostBlockPayloadException($message);
        }
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function requireAnyNonEmptyString(array $values, string $message): void
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return;
            }
        }

        throw new InvalidPostBlockPayloadException($message);
    }

    private function requireNonEmptyArray(mixed $value, string $message): void
    {
        if (! is_array($value) || $value === []) {
            throw new InvalidPostBlockPayloadException($message);
        }
    }
}
