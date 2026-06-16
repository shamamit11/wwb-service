<?php

namespace App\Modules\Templates\Services;

use App\Enums\ContentBlockType;
use App\Models\Template;
use App\Models\TemplateBlock;
use App\Modules\Templates\Data\TemplatePayloadContextData;
use Illuminate\Support\Str;

class TemplatePayloadFactory
{
    /**
     * @return array<string, mixed>
     */
    public function buildPreviewPayload(Template $template, TemplatePayloadContextData $context): array
    {
        $resolved = $this->resolveContext($template, $context);

        return [
            'title' => $resolved['title'],
            'topic' => $resolved['topic'],
            'meta' => $template->default_meta ?? [],
            'blocks' => $this->buildBlocks($template, $resolved),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSeedPostPayload(Template $template, TemplatePayloadContextData $context): array
    {
        $resolved = $this->resolveContext($template, $context);

        return [
            'title' => $resolved['title'],
            'slug' => Str::slug($resolved['title']),
            'status' => Template::STATUS_DRAFT,
            'template_id' => $template->id,
            'template_type' => $template->template_type,
            'excerpt_prompt' => $template->default_excerpt_prompt,
            'meta' => $template->default_meta ?? [],
            'blocks' => $this->buildBlocks($template, $resolved),
        ];
    }

    /**
     * @param  array{title:string,topic:string}  $context
     * @return array<int, array<string, mixed>>
     */
    private function buildBlocks(Template $template, array $context): array
    {
        $template->loadMissing('blocks');

        return $template->blocks
            ->sortBy('sort_order')
            ->values()
            ->map(fn (TemplateBlock $block): array => [
                'block_key' => $block->block_key,
                'block_type' => $block->block_type,
                'sort_order' => $block->sort_order,
                'label' => $block->label,
                'settings' => $block->settings ?? [],
                'is_required' => $block->is_required,
                'content' => $this->buildBlockContent($template, $block, $context),
            ])
            ->all();
    }

    /**
     * @param  array{title:string,topic:string}  $context
     * @return array<string, mixed>
     */
    private function buildBlockContent(Template $template, TemplateBlock $block, array $context): array
    {
        $settings = $this->normalizeSettings($block->settings);
        $markdown = $this->replacePlaceholders($block->default_markdown, $template, $context);
        $headline = $this->markdownHeadline($markdown);
        $topic = $context['topic'];
        $title = $context['title'];
        $blockLabel = $block->label ?: $title;

        return match ($block->block_type) {
            ContentBlockType::HEADING->value => [
                'text' => $headline !== '' ? $headline : $title,
                'level' => $settings['level'] ?? $this->markdownHeadingLevel($markdown) ?? 2,
            ],
            ContentBlockType::PARAGRAPH->value => [
                'markdown' => $markdown ?: "Introduce {$topic} with practical context and expected outcomes.",
            ],
            ContentBlockType::IMAGE->value => [
                'url' => 'https://example.test/media/'.Str::slug($template->slug.'-'.$blockLabel).'.jpg',
                'alt_text' => $blockLabel,
                'caption' => $markdown ?: "Illustration for {$topic}.",
            ],
            ContentBlockType::QUOTE->value => [
                'quote_markdown' => $markdown ?: "A concise editorial insight about {$topic}.",
                'attribution' => $settings['attribution'] ?? 'Editorial note',
            ],
            ContentBlockType::LIST->value => [
                'items' => $this->buildListItems($markdown, $topic),
            ],
            ContentBlockType::CODE->value => [
                'language' => $settings['language'] ?? 'text',
                'code' => $markdown ?: "// Example snippet for {$topic}\n",
            ],
            ContentBlockType::FAQ->value => [
                'items' => [[
                    'question' => $block->label ?: "What should readers know about {$topic}?",
                    'answer_markdown' => $markdown ?: "Summarize the practical implications of {$topic}.",
                ]],
            ],
            ContentBlockType::CALLOUT->value => [
                'markdown' => $markdown ?: "Important note about {$topic}.",
                'variant' => $settings['variant'] ?? 'info',
            ],
            default => [
                'markdown' => $markdown ?: "Placeholder content for {$topic}.",
            ],
        };
    }

    /**
     * @return array{title:string,topic:string}
     */
    private function resolveContext(Template $template, TemplatePayloadContextData $context): array
    {
        $type = Str::headline($template->template_type);

        return [
            'title' => $context->title ?: "{$type} Preview",
            'topic' => $context->topic ?: "{$type} topic",
        ];
    }

    /**
     * @return list<string>
     */
    private function buildListItems(?string $markdown, string $topic): array
    {
        if ($markdown !== null && $markdown !== '') {
            $items = collect(preg_split('/\r\n|\r|\n/', $markdown) ?: [])
                ->map(fn (string $line): string => trim(Str::of($line)->ltrim('-*0123456789. ')))
                ->filter()
                ->values()
                ->all();

            if ($items !== []) {
                return $items;
            }
        }

        return [
            "Key point one about {$topic}",
            "Key point two about {$topic}",
            "Key point three about {$topic}",
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeSettings(mixed $settings): array
    {
        return is_array($settings) ? $settings : [];
    }

    private function replacePlaceholders(?string $markdown, Template $template, array $context): ?string
    {
        if ($markdown === null || $markdown === '') {
            return $markdown;
        }

        return strtr($markdown, [
            '{{title}}' => $context['title'],
            '{{topic}}' => $context['topic'],
            '{{template_name}}' => $template->name,
            '{{template_type}}' => $template->template_type,
        ]);
    }

    private function markdownHeadline(?string $markdown): string
    {
        if ($markdown === null || $markdown === '') {
            return '';
        }

        return trim((string) Str::of($markdown)->replaceMatches('/^#+\s*/', ''));
    }

    private function markdownHeadingLevel(?string $markdown): ?int
    {
        if ($markdown === null || $markdown === '' || ! preg_match('/^(#+)\s+/', $markdown, $matches)) {
            return null;
        }

        return strlen($matches[1]);
    }
}
