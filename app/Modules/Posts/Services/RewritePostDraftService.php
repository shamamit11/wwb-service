<?php

namespace App\Modules\Posts\Services;

use App\AI\Agents\DraftRewriteAgent;
use App\AI\DTO\PostRewriteInput;
use App\AI\DTO\PostRewriteResult;
use App\Enums\ContentBlockType;
use App\Models\ContentBrief;
use App\Models\Post;
use App\Models\PostBlock;
use App\Models\Tag;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use App\Modules\Posts\Data\PostBlockPayloadData;
use App\Modules\Posts\Data\UpdatePostCommandData;
use App\Modules\Seo\Data\UpdateSeoMetadataData;
use App\Modules\Seo\Services\UpsertSeoMetadataService;
use App\Modules\Tags\Repositories\TagRepository;
use Illuminate\Support\Str;
use RuntimeException;

class RewritePostDraftService
{
    public const SCOPE_FULL_DRAFT = 'full_draft';

    public const SCOPE_SECTION = 'section';

    public const SCOPE_PARAGRAPH = 'paragraph';

    public function __construct(
        private readonly DraftRewriteAgent $agent,
        private readonly ContentBriefRepository $briefs,
        private readonly KnowledgeContextService $knowledgeContext,
        private readonly UpdatePostService $updatePost,
        private readonly UpsertSeoMetadataService $upsertSeoMetadata,
        private readonly TagRepository $tags,
    ) {}

    public function handle(
        Post $post,
        string $scope,
        array $targetBlockIds = [],
        ?string $instructions = null,
        ?int $aiJobId = null,
        ?string $promptTemplateKey = null,
    ): Post {
        $post = $post->loadMissing(['author', 'category', 'template', 'featuredMedia', 'tags', 'blocks', 'seo']);
        $brief = $this->resolveSourceBrief($post);
        $targetBlockIds = array_values(array_unique(array_map(static fn (mixed $id): int => (int) $id, $targetBlockIds)));
        $existingBlocks = $this->serializeExistingBlocks($post);
        $targetBlocks = $this->extractTargetBlocks($post, $scope, $targetBlockIds);

        $result = $this->agent->run(new PostRewriteInput(
            postId: (int) $post->id,
            contentBriefId: (int) $brief->id,
            contentTopicId: (int) $brief->content_topic_id,
            scope: $scope,
            postTitle: $post->title,
            postSlug: $post->slug,
            postExcerpt: $post->excerpt,
            primaryKeyword: $this->normalizeString($post->meta['primary_keyword'] ?? null),
            secondaryKeywords: $this->normalizeStringList($post->meta['secondary_keywords'] ?? []),
            searchIntent: $this->normalizeString($post->meta['search_intent'] ?? null),
            instructions: $instructions,
            existingMarkdownBody: $this->buildMarkdownBody($existingBlocks),
            existingContentBlocks: $existingBlocks,
            targetBlockIds: $targetBlockIds,
            targetBlocks: $targetBlocks,
            knowledgeBaseContext: $this->knowledgeContext->forPrompt(new KnowledgeContextQueryData(
                subject: $post->title,
                keywords: array_values(array_filter([
                    $post->meta['primary_keyword'] ?? null,
                    ...($post->meta['secondary_keywords'] ?? []),
                    $brief->primary_keyword,
                    ...($brief->secondary_keywords ?? []),
                    $brief->topic?->cluster,
                ], static fn (mixed $value): bool => is_string($value) && trim($value) !== '')),
                maxEntries: 8,
                maxEntryCharacters: 340,
                maxTotalCharacters: 2200,
            )),
            briefOutline: is_array($brief->outline) ? $brief->outline : [],
            briefHeadings: is_array($brief->headings) ? $brief->headings : [],
            faqSuggestions: is_array($brief->faq_suggestions) ? $brief->faq_suggestions : [],
            metadata: array_filter([
                'ai_job_id' => $aiJobId,
                'prompt_template_key' => $promptTemplateKey,
            ], static fn (mixed $value): bool => $value !== null),
        ));

        if (! $result->isSuccessful() || ! $result->parsedResponse instanceof PostRewriteResult) {
            throw new RuntimeException($result->error?->message ?? 'Draft rewrite agent failed to rewrite the post.');
        }

        $updatedBlocks = $scope === self::SCOPE_FULL_DRAFT
            ? $this->resequenceBlocks($result->parsedResponse->contentBlocks)
            : $this->mergePartialBlocks($existingBlocks, $targetBlockIds, $result->parsedResponse->contentBlocks);

        $meta = $this->buildMeta($post, $result->parsedResponse, $updatedBlocks, $scope, $targetBlockIds, $instructions, $aiJobId);
        $tagIds = $this->resolveTagIds($post, $result->parsedResponse);

        $updated = $this->updatePost->handle($post, new UpdatePostCommandData(
            authorUserId: (int) $post->author_user_id,
            categoryId: (int) $post->category_id,
            templateId: $post->template_id ? (int) $post->template_id : null,
            featuredMediaId: $post->featured_media_id ? (int) $post->featured_media_id : null,
            title: $scope === self::SCOPE_FULL_DRAFT ? ($result->parsedResponse->title ?? $post->title) : $post->title,
            slug: $scope === self::SCOPE_FULL_DRAFT ? ($result->parsedResponse->slug ?? $post->slug) : $post->slug,
            excerpt: $scope === self::SCOPE_FULL_DRAFT ? ($result->parsedResponse->excerpt ?? $post->excerpt) : $post->excerpt,
            status: Post::STATUS_DRAFT,
            visibility: $post->visibility,
            publishedAt: null,
            scheduledFor: null,
            contentVersion: max(1, (int) $post->content_version) + 1,
            readingTimeMinutes: $this->readingTimeMinutes($meta['markdown_body'] ?? ''),
            wordCount: $this->wordCount($meta['markdown_body'] ?? ''),
            isFeatured: (bool) $post->is_featured,
            meta: $meta,
            tagIds: $tagIds,
            blocks: $this->toPayloadBlocks($updatedBlocks),
        ));

        $this->upsertSeoMetadata->handle('post', (int) $updated->id, new UpdateSeoMetadataData(
            metaTitle: $scope === self::SCOPE_FULL_DRAFT ? ($result->parsedResponse->seoTitle ?? $updated->seo?->meta_title) : ($updated->seo?->meta_title),
            metaDescription: $scope === self::SCOPE_FULL_DRAFT ? ($result->parsedResponse->metaDescription ?? $updated->seo?->meta_description) : ($updated->seo?->meta_description),
            canonicalUrl: null,
            robotsIndex: $updated->seo?->robots_index ?? true,
            robotsFollow: $updated->seo?->robots_follow ?? true,
            ogTitle: $scope === self::SCOPE_FULL_DRAFT ? ($result->parsedResponse->seoTitle ?? $updated->seo?->og_title) : ($updated->seo?->og_title),
            ogDescription: $scope === self::SCOPE_FULL_DRAFT ? ($result->parsedResponse->metaDescription ?? $updated->seo?->og_description) : ($updated->seo?->og_description),
            ogImageMediaId: $updated->seo?->og_image_media_id,
            schemaType: $updated->seo?->schema_type,
            schemaPayload: $updated->seo?->schema_payload,
            focusKeyword: $this->normalizeString($meta['primary_keyword'] ?? null),
        ));

        return $updated->refresh()->load(['author', 'category', 'template', 'featuredMedia', 'tags', 'blocks.sourceTemplateBlock', 'seo']);
    }

    private function resolveSourceBrief(Post $post): ContentBrief
    {
        $meta = is_array($post->meta) ? $post->meta : [];
        $briefId = $meta['source_content_brief_id'] ?? null;

        if (! is_numeric($briefId) || (int) $briefId <= 0) {
            throw new RuntimeException('Post rewrite requires a source content brief reference.');
        }

        $brief = $this->briefs->findById((int) $briefId);

        if (! $brief instanceof ContentBrief) {
            throw new RuntimeException("Content brief [{$briefId}] could not be found.");
        }

        return $brief->loadMissing('topic');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function serializeExistingBlocks(Post $post): array
    {
        return $post->blocks
            ->sortBy('sort_order')
            ->values()
            ->map(fn (PostBlock $block): array => $this->serializeBlock($block))
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extractTargetBlocks(Post $post, string $scope, array $targetBlockIds): array
    {
        if ($scope === self::SCOPE_FULL_DRAFT) {
            return [];
        }

        $blocks = $post->blocks
            ->sortBy('sort_order')
            ->values()
            ->whereIn('id', $targetBlockIds)
            ->map(fn (PostBlock $block): array => $this->serializeBlock($block))
            ->values()
            ->all();

        if ($blocks === []) {
            throw new RuntimeException('No matching target blocks were found for the requested draft rewrite.');
        }

        if ($scope === self::SCOPE_PARAGRAPH && count($blocks) !== 1) {
            throw new RuntimeException('Paragraph rewrite requires exactly one target block.');
        }

        if ($scope === self::SCOPE_PARAGRAPH && ($blocks[0]['block_type'] ?? null) !== ContentBlockType::PARAGRAPH->value) {
            throw new RuntimeException('Paragraph rewrite requires the target block to be a paragraph block.');
        }

        if ($scope === self::SCOPE_SECTION) {
            $sortOrders = array_values(array_map(
                static fn (array $block): int => (int) ($block['sort_order'] ?? 0),
                $blocks,
            ));

            foreach ($sortOrders as $index => $sortOrder) {
                if ($index > 0 && $sortOrder !== ($sortOrders[$index - 1] + 1)) {
                    throw new RuntimeException('Section rewrite requires contiguous target blocks.');
                }
            }
        }

        return $blocks;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeBlock(PostBlock $block): array
    {
        $settings = is_array($block->settings) ? $block->settings : [];
        $contentMarkdown = $block->content_markdown;
        $content = match ($block->block_type) {
            ContentBlockType::HEADING->value => [
                'text' => $block->plain_text_cache ?? ltrim((string) $contentMarkdown, "# \t\n\r\0\x0B"),
                'level' => (int) ($settings['level'] ?? 2),
            ],
            ContentBlockType::PARAGRAPH->value => [
                'markdown' => $contentMarkdown,
            ],
            ContentBlockType::IMAGE->value => array_merge($settings, ['caption' => $contentMarkdown]),
            ContentBlockType::QUOTE->value => [
                'quote_markdown' => $contentMarkdown,
                'attribution' => $settings['attribution'] ?? null,
            ],
            ContentBlockType::LIST->value => [
                'items' => is_array($settings['items'] ?? null) ? array_values($settings['items']) : [],
            ],
            ContentBlockType::CODE->value => [
                'code' => $contentMarkdown,
                'language' => $settings['language'] ?? null,
            ],
            ContentBlockType::FAQ->value => [
                'items' => is_array($settings['items'] ?? null) ? array_values($settings['items']) : [],
            ],
            ContentBlockType::CALLOUT->value => [
                'markdown' => $contentMarkdown,
                'variant' => $settings['variant'] ?? null,
            ],
            default => [
                'markdown' => $contentMarkdown,
            ],
        };

        return [
            'id' => (int) $block->id,
            'block_type' => $block->block_type,
            'sort_order' => (int) $block->sort_order,
            'content' => $content,
            'source_template_block_id' => $block->source_template_block_id ? (int) $block->source_template_block_id : null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $existingBlocks
     * @param  list<int>  $targetBlockIds
     * @param  list<array<string, mixed>>  $replacementBlocks
     * @return list<array<string, mixed>>
     */
    private function mergePartialBlocks(array $existingBlocks, array $targetBlockIds, array $replacementBlocks): array
    {
        $firstIndex = null;
        $merged = [];
        $replacementInserted = false;

        foreach ($existingBlocks as $index => $block) {
            $blockId = isset($block['id']) && is_numeric($block['id']) ? (int) $block['id'] : null;

            if ($blockId !== null && in_array($blockId, $targetBlockIds, true)) {
                if ($firstIndex === null) {
                    $firstIndex = $index;
                }

                if (! $replacementInserted) {
                    foreach ($replacementBlocks as $replacementBlock) {
                        $merged[] = [
                            'block_type' => $replacementBlock['block_type'],
                            'content' => is_array($replacementBlock['content'] ?? null) ? $replacementBlock['content'] : [],
                            'source_template_block_id' => $block['source_template_block_id'] ?? null,
                        ];
                    }

                    $replacementInserted = true;
                }

                continue;
            }

            $merged[] = [
                'block_type' => $block['block_type'],
                'content' => is_array($block['content'] ?? null) ? $block['content'] : [],
                'source_template_block_id' => $block['source_template_block_id'] ?? null,
            ];
        }

        if ($firstIndex === null) {
            throw new RuntimeException('Draft rewrite could not locate the selected blocks in the post.');
        }

        return $this->resequenceBlocks($merged);
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @return list<array<string, mixed>>
     */
    private function resequenceBlocks(array $blocks): array
    {
        return array_values(array_map(
            static fn (array $block, int $index): array => [
                'block_type' => (string) $block['block_type'],
                'sort_order' => $index + 1,
                'content' => is_array($block['content'] ?? null) ? $block['content'] : [],
                'source_template_block_id' => isset($block['source_template_block_id']) && is_numeric($block['source_template_block_id']) ? (int) $block['source_template_block_id'] : null,
            ],
            $blocks,
            array_keys($blocks),
        ));
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @return list<PostBlockPayloadData>
     */
    private function toPayloadBlocks(array $blocks): array
    {
        return array_map(
            static fn (array $block): PostBlockPayloadData => new PostBlockPayloadData(
                blockType: (string) $block['block_type'],
                sortOrder: (int) $block['sort_order'],
                content: is_array($block['content'] ?? null) ? $block['content'] : [],
                sourceTemplateBlockId: isset($block['source_template_block_id']) && is_numeric($block['source_template_block_id']) ? (int) $block['source_template_block_id'] : null,
            ),
            $blocks,
        );
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @return array<string, mixed>
     */
    private function buildMeta(
        Post $post,
        PostRewriteResult $result,
        array $blocks,
        string $scope,
        array $targetBlockIds,
        ?string $instructions,
        ?int $aiJobId,
    ): array {
        $existingMeta = is_array($post->meta) ? $post->meta : [];
        $rewriteCount = is_numeric($existingMeta['rewrite_count'] ?? null) ? ((int) $existingMeta['rewrite_count']) + 1 : 1;

        return array_merge($existingMeta, [
            'markdown_body' => $this->buildMarkdownBody($blocks),
            'suggested_tags' => $result->suggestedTags !== [] ? $result->suggestedTags : ($existingMeta['suggested_tags'] ?? []),
            'image_placement_notes' => $result->imagePlacementNotes !== [] ? $result->imagePlacementNotes : ($existingMeta['image_placement_notes'] ?? []),
            'alt_text_suggestions' => $result->altTextSuggestions !== [] ? $result->altTextSuggestions : ($existingMeta['alt_text_suggestions'] ?? []),
            'ai_job_id' => $aiJobId,
            'generated_by' => 'DraftRewriteAgent',
            'rewrite_count' => $rewriteCount,
            'last_rewrite_scope' => $scope,
            'last_rewrite_target_block_ids' => array_values($targetBlockIds),
            'last_rewrite_instructions' => $instructions,
            'last_rewrite_at' => now()->toISOString(),
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     */
    private function buildMarkdownBody(array $blocks): string
    {
        $parts = [];

        foreach ($blocks as $block) {
            $parts[] = $this->blockMarkdown($block);
        }

        return trim(implode("\n\n", array_values(array_filter($parts, static fn (?string $part): bool => $part !== null && $part !== ''))));
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function blockMarkdown(array $block): ?string
    {
        $content = is_array($block['content'] ?? null) ? $block['content'] : [];

        return match ($block['block_type'] ?? null) {
            ContentBlockType::HEADING->value => ($text = $this->normalizeString($content['text'] ?? null)) !== null
                ? str_repeat('#', max(1, min(6, (int) ($content['level'] ?? 2)))).' '.$text
                : null,
            ContentBlockType::PARAGRAPH->value, ContentBlockType::CALLOUT->value => $this->normalizeString($content['markdown'] ?? null),
            ContentBlockType::QUOTE->value => $this->normalizeString($content['quote_markdown'] ?? null),
            ContentBlockType::LIST->value => ($items = $this->normalizeStringList($content['items'] ?? [])) !== []
                ? collect($items)->map(fn (string $item): string => "- {$item}")->implode("\n")
                : null,
            ContentBlockType::CODE->value => $this->normalizeString($content['code'] ?? null),
            ContentBlockType::FAQ->value => ($items = is_array($content['items'] ?? null) ? $content['items'] : []) !== []
                ? implode("\n\n", array_values(array_filter(array_map(function (mixed $item): ?string {
                    if (! is_array($item)) {
                        return null;
                    }

                    $question = $this->normalizeString($item['question'] ?? null);
                    $answer = $this->normalizeString($item['answer_markdown'] ?? null);

                    if ($question === null || $answer === null) {
                        return null;
                    }

                    return "## {$question}\n{$answer}";
                }, $items))))
                : null,
            ContentBlockType::IMAGE->value => $this->normalizeString($content['caption'] ?? null),
            default => null,
        };
    }

    /**
     * @return list<int>
     */
    private function resolveTagIds(Post $post, PostRewriteResult $result): array
    {
        if ($result->suggestedTags === []) {
            return $post->tags->modelKeys();
        }

        $lookup = [];

        foreach ($this->tags->getActiveOrdered() as $tag) {
            if (! $tag instanceof Tag) {
                continue;
            }

            $lookup[mb_strtolower($tag->name)] = (int) $tag->id;
            $lookup[mb_strtolower($tag->slug)] = (int) $tag->id;
        }

        $tagIds = $post->tags->modelKeys();

        foreach ($result->suggestedTags as $suggestedTag) {
            $normalized = mb_strtolower(trim($suggestedTag));

            if ($normalized === '') {
                continue;
            }

            $slug = mb_strtolower((string) Str::slug($suggestedTag));

            if (isset($lookup[$normalized])) {
                $tagIds[] = $lookup[$normalized];
                continue;
            }

            if (isset($lookup[$slug])) {
                $tagIds[] = $lookup[$slug];
            }
        }

        return array_values(array_unique($tagIds));
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

        return array_values(array_filter(array_map(
            fn (mixed $item): ?string => $this->normalizeString($item),
            $value,
        )));
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    private function wordCount(string $markdown): int
    {
        preg_match_all('/\pL[\pL\pN\'_-]*/u', strip_tags($markdown), $matches);

        return count($matches[0]);
    }

    private function readingTimeMinutes(string $markdown): int
    {
        return max(1, (int) ceil($this->wordCount($markdown) / 200));
    }
}
