<?php

namespace App\AI\Tools;

use App\AI\DTO\BlogDraftResult;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\Post;
use App\Models\Tag;
use App\Modules\ContentBriefs\Data\UpdateContentBriefData;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\ContentBriefs\Services\UpdateContentBriefService;
use App\Modules\ContentTopics\Services\MarkContentTopicUsedService;
use App\Modules\Posts\Data\CreatePostCommandData;
use App\Modules\Posts\Data\PostBlockPayloadData;
use App\Modules\Posts\Data\UpdatePostCommandData;
use App\Modules\Posts\Repositories\PostRepository;
use App\Modules\Posts\Services\CreatePostService;
use App\Modules\Posts\Services\UpdatePostService;
use App\Modules\Seo\Data\UpdateSeoMetadataData;
use App\Modules\Seo\Services\UpsertSeoMetadataService;
use App\Modules\Tags\Repositories\TagRepository;
use RuntimeException;

class SavePostDraftTool
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
        private readonly PostRepository $posts,
        private readonly TagRepository $tags,
        private readonly CreatePostService $createPost,
        private readonly UpdatePostService $updatePost,
        private readonly UpsertSeoMetadataService $upsertSeoMetadata,
        private readonly UpdateContentBriefService $updateContentBrief,
        private readonly MarkContentTopicUsedService $markTopicUsed,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function save(
        int $contentBriefId,
        int $contentTopicId,
        ?string $primaryKeyword,
        array $secondaryKeywords,
        ?string $searchIntent,
        BlogDraftResult $result,
        array $metadata = [],
    ): Post {
        $brief = $this->briefs->findById($contentBriefId);

        if (! $brief instanceof ContentBrief) {
            throw new RuntimeException("Content brief [{$contentBriefId}] was not found.");
        }

        $authorUserId = $this->resolveAuthorUserId($metadata['author_user_id'] ?? null);
        $categoryId = $this->requirePositiveInt($metadata['category_id'] ?? null, 'category_id');
        $templateId = $this->nullablePositiveInt($metadata['template_id'] ?? null, 'template_id');
        $featuredMediaId = $this->nullablePositiveInt($metadata['featured_media_id'] ?? null, 'featured_media_id');
        $visibility = $this->normalizeVisibility($metadata['visibility'] ?? null);

        $existing = $this->posts->findBySourceContentBriefId($contentBriefId);
        $matchedTagIds = $this->resolveSuggestedTagIds($result->suggestedTags);
        $tagIds = $existing instanceof Post
            ? array_values(array_unique(array_merge($existing->tags->modelKeys(), $matchedTagIds)))
            : $matchedTagIds;

        $meta = $this->buildMeta(
            existingMeta: $existing?->meta,
            contentBriefId: $contentBriefId,
            contentTopicId: $contentTopicId,
            primaryKeyword: $primaryKeyword,
            secondaryKeywords: $secondaryKeywords,
            searchIntent: $searchIntent,
            result: $result,
        );

        $blocks = array_map(
            static fn (array $block): PostBlockPayloadData => new PostBlockPayloadData(
                blockType: (string) $block['block_type'],
                sortOrder: (int) $block['sort_order'],
                content: is_array($block['content'] ?? null) ? $block['content'] : [],
            ),
            $result->contentBlocks,
        );

        if ($existing instanceof Post) {
            $post = $this->updatePost->handle($existing, new UpdatePostCommandData(
                authorUserId: $authorUserId,
                categoryId: $categoryId,
                templateId: $templateId,
                featuredMediaId: $featuredMediaId,
                title: $result->title,
                slug: $result->slug,
                excerpt: $result->excerpt,
                status: Post::STATUS_DRAFT,
                visibility: $visibility,
                publishedAt: null,
                scheduledFor: null,
                contentVersion: max(1, (int) $existing->content_version) + 1,
                readingTimeMinutes: $this->readingTimeMinutes($result->markdownBody),
                wordCount: $this->wordCount($result->markdownBody),
                isFeatured: false,
                meta: $meta,
                tagIds: $tagIds,
                blocks: $blocks,
            ));
        } else {
            $post = $this->createPost->handle(new CreatePostCommandData(
                authorUserId: $authorUserId,
                categoryId: $categoryId,
                templateId: $templateId,
                featuredMediaId: $featuredMediaId,
                title: $result->title,
                slug: $result->slug,
                excerpt: $result->excerpt,
                status: Post::STATUS_DRAFT,
                visibility: $visibility,
                publishedAt: null,
                scheduledFor: null,
                contentVersion: 1,
                readingTimeMinutes: $this->readingTimeMinutes($result->markdownBody),
                wordCount: $this->wordCount($result->markdownBody),
                isFeatured: false,
                meta: $meta,
                tagIds: $tagIds,
                blocks: $blocks,
            ));
        }

        $this->upsertSeoMetadata->handle('post', (int) $post->id, new UpdateSeoMetadataData(
            metaTitle: $result->seoTitle,
            metaDescription: $result->metaDescription,
            canonicalUrl: null,
            robotsIndex: true,
            robotsFollow: true,
            ogTitle: $result->seoTitle,
            ogDescription: $result->metaDescription,
            ogImageMediaId: null,
            schemaType: null,
            schemaPayload: null,
            focusKeyword: $primaryKeyword,
        ));

        $this->markBriefAndTopicUsed($brief);

        return $post->refresh()->load(['author', 'category', 'template', 'featuredMedia', 'tags', 'blocks.sourceTemplateBlock', 'seo']);
    }

    /**
     * @param  array<string, mixed>|null  $existingMeta
     * @param  list<string>  $secondaryKeywords
     * @return array<string, mixed>
     */
    private function buildMeta(
        ?array $existingMeta,
        int $contentBriefId,
        int $contentTopicId,
        ?string $primaryKeyword,
        array $secondaryKeywords,
        ?string $searchIntent,
        BlogDraftResult $result,
    ): array {
        return array_merge($existingMeta ?? [], [
            'source_content_brief_id' => $contentBriefId,
            'source_content_topic_id' => $contentTopicId,
            'primary_keyword' => $primaryKeyword,
            'secondary_keywords' => array_values($secondaryKeywords),
            'search_intent' => $searchIntent,
            'markdown_body' => $result->markdownBody,
            'faq_suggestions' => $result->faqSuggestions,
            'suggested_tags' => $result->suggestedTags,
            'image_placement_notes' => $result->imagePlacementNotes,
            'alt_text_suggestions' => $result->altTextSuggestions,
            'generated_by' => 'BlogWriterAgent',
        ]);
    }

    /**
     * @param  list<string>  $suggestedTags
     * @return list<int>
     */
    private function resolveSuggestedTagIds(array $suggestedTags): array
    {
        if ($suggestedTags === []) {
            return [];
        }

        $lookup = [];

        foreach ($this->tags->getActiveOrdered() as $tag) {
            if (! $tag instanceof Tag) {
                continue;
            }

            $lookup[mb_strtolower($tag->name)] = (int) $tag->id;
            $lookup[mb_strtolower($tag->slug)] = (int) $tag->id;
        }

        $tagIds = [];

        foreach ($suggestedTags as $suggestedTag) {
            $normalized = mb_strtolower(trim($suggestedTag));

            if ($normalized === '') {
                continue;
            }

            $slug = mb_strtolower((string) \Illuminate\Support\Str::slug($suggestedTag));

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

    private function markBriefAndTopicUsed(ContentBrief $brief): void
    {
        if ($brief->status === ContentBrief::STATUS_APPROVED) {
            $brief = $this->updateContentBrief->handle($brief, new UpdateContentBriefData(
                status: ContentBrief::STATUS_USED,
            ));
        }

        $topic = $brief->topic;

        if ($topic instanceof ContentTopic && $topic->status === ContentTopic::STATUS_APPROVED) {
            $this->markTopicUsed->handle($topic);
        }
    }

    private function requirePositiveInt(mixed $value, string $field): int
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        throw new RuntimeException("Blog draft persistence requires a valid [{$field}] metadata value.");
    }

    private function resolveAuthorUserId(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 1;
        }

        return $this->requirePositiveInt($value, 'author_user_id');
    }

    private function nullablePositiveInt(mixed $value, string $field = 'value'): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->requirePositiveInt($value, $field);
    }

    private function normalizeVisibility(mixed $value): string
    {
        if (is_string($value) && in_array($value, Post::VISIBILITIES, true)) {
            return $value;
        }

        return Post::VISIBILITY_PUBLIC;
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
