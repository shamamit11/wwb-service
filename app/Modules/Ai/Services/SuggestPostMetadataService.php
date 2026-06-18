<?php

namespace App\Modules\Ai\Services;

use App\AI\Agents\MetadataSuggestionAgent;
use App\AI\DTO\AgentResult;
use App\AI\DTO\PostMetadataSuggestionInput;
use App\Models\Post;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use App\Modules\Posts\Repositories\PostRepository;
use RuntimeException;

class SuggestPostMetadataService
{
    public function __construct(
        private readonly MetadataSuggestionAgent $agent,
        private readonly KnowledgeContextService $knowledgeContext,
        private readonly PostRepository $posts,
    ) {}

    public function handle(
        Post $post,
        ?string $instructions = null,
        ?int $aiJobId = null,
        ?string $promptTemplateKey = null,
    ): AgentResult {
        $post = $this->posts->findById((int) $post->id) ?? $post->loadMissing(['tags', 'blocks', 'seo']);
        $meta = is_array($post->meta) ? $post->meta : [];

        $result = $this->agent->run(new PostMetadataSuggestionInput(
            postId: (int) $post->id,
            postTitle: $post->title,
            postSlug: $post->slug,
            postExcerpt: $post->excerpt,
            postStatus: $post->status,
            primaryKeyword: $this->normalizeString($meta['primary_keyword'] ?? null),
            secondaryKeywords: $this->normalizeStringList($meta['secondary_keywords'] ?? []),
            existingFocusKeyword: $post->seo?->focus_keyword,
            existingMetaTitle: $post->seo?->meta_title,
            existingMetaDescription: $post->seo?->meta_description,
            existingMarkdownBody: $this->resolveMarkdownBody($post, $meta),
            existingTags: $post->tags->pluck('name')->filter()->values()->all(),
            knowledgeBaseContext: $this->knowledgeContext->forPrompt(new KnowledgeContextQueryData(
                subject: $post->title,
                keywords: array_values(array_filter([
                    $meta['primary_keyword'] ?? null,
                    ...($meta['secondary_keywords'] ?? []),
                    $post->seo?->focus_keyword,
                ], static fn (mixed $value): bool => is_string($value) && trim($value) !== '')),
                maxEntries: 6,
                maxEntryCharacters: 280,
                maxTotalCharacters: 1800,
            )),
            briefOutline: $this->resolveBriefOutline($meta),
            briefHeadings: $this->resolveBriefHeadings($meta),
            instructions: $instructions,
            metadata: array_filter([
                'ai_job_id' => $aiJobId,
                'prompt_template_key' => $promptTemplateKey,
            ], static fn (mixed $value): bool => $value !== null),
        ));

        if (! $result->isSuccessful()) {
            throw new RuntimeException($result->error?->message ?? 'Metadata suggestion agent failed.');
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function resolveMarkdownBody(Post $post, array $meta): string
    {
        $markdown = $this->normalizeString($meta['markdown_body'] ?? null);

        if ($markdown !== null) {
            return $markdown;
        }

        $blocks = $post->blocks->sortBy('sort_order');
        $parts = [];

        foreach ($blocks as $block) {
            $content = trim((string) ($block->content_markdown ?? ''));

            if ($content !== '') {
                $parts[] = $content;
            }
        }

        return trim(implode("\n\n", $parts));
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<array<string, mixed>>
     */
    private function resolveBriefOutline(array $meta): array
    {
        $outline = $meta['brief_outline'] ?? null;

        return is_array($outline) ? array_values(array_filter($outline, 'is_array')) : [];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<string>
     */
    private function resolveBriefHeadings(array $meta): array
    {
        return $this->normalizeStringList($meta['brief_headings'] ?? []);
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    private function normalizeStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(function (mixed $item): ?string {
            if (! is_string($item)) {
                return null;
            }

            $normalized = trim($item);

            return $normalized !== '' ? $normalized : null;
        }, $value)));
    }
}
