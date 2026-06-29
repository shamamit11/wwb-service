<?php

namespace App\AI\Tools;

use App\AI\DTO\BlogDraftResult;
use App\Models\ContentTopic;
use App\Models\Post;
use App\Models\Tag;
use App\Modules\ContentTopics\Services\MarkContentTopicUsedService;
use App\Modules\Posts\Data\CreatePostCommandData;
use App\Modules\Posts\Data\UpdatePostCommandData;
use App\Modules\Posts\Repositories\PostRepository;
use App\Modules\Posts\Services\AssessPostOriginalityService;
use App\Modules\Posts\Services\CreatePostService;
use App\Modules\Posts\Services\UpdatePostService;
use App\Modules\Seo\Data\UpdateSeoMetadataData;
use App\Modules\Seo\Services\UpsertSeoMetadataService;
use App\Modules\Tags\Repositories\TagRepository;
use Illuminate\Support\Str;
use RuntimeException;

class SavePostDraftTool
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly TagRepository $tags,
        private readonly CreatePostService $createPost,
        private readonly UpdatePostService $updatePost,
        private readonly AssessPostOriginalityService $assessPostOriginality,
        private readonly UpsertSeoMetadataService $upsertSeoMetadata,
        private readonly MarkContentTopicUsedService $markTopicUsed,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function save(
        int $contentTopicId,
        ?string $primaryKeyword,
        array $secondaryKeywords,
        ?string $searchIntent,
        BlogDraftResult $result,
        array $metadata = [],
    ): Post {
        $authorUserId = $this->resolveAuthorUserId($metadata['author_user_id'] ?? null);
        $categoryId = $this->requirePositiveInt($metadata['category_id'] ?? null, 'category_id');
        $featuredMediaId = $this->nullablePositiveInt($metadata['featured_media_id'] ?? null, 'featured_media_id');
        $visibility = $this->normalizeVisibility($metadata['visibility'] ?? null);

        $existing = $this->posts->findBySourceContentTopicId($contentTopicId);
        $matchedTagIds = $this->resolveSuggestedTagIds($result->suggestedTags);
        $tagIds = $existing instanceof Post
            ? array_values(array_unique(array_merge($existing->tags->modelKeys(), $matchedTagIds)))
            : $matchedTagIds;

        $meta = $this->buildMeta(
            existingMeta: $existing?->getAttributeValue('meta'),
            contentTopicId: $contentTopicId,
            primaryKeyword: $primaryKeyword,
            secondaryKeywords: $secondaryKeywords,
            searchIntent: $searchIntent,
            result: $result,
            metadata: $metadata,
        );

        if ($existing instanceof Post) {
            $post = $this->updatePost->handle($existing, new UpdatePostCommandData(
                authorUserId: $authorUserId,
                categoryId: $categoryId,
                featuredMediaId: $featuredMediaId,
                title: $result->title,
                slug: $result->slug,
                shortDescription: $result->shortDescription,
                description: $result->description,
                fullArticleHtml: $result->fullArticleHtml,
                fullArticleDelta: $result->fullArticleDelta,
                faq: array_map(
                    static fn (array $faq): array => [
                        'question' => $faq['question'],
                        'answer' => $faq['answer_markdown'],
                    ],
                    $result->faqSuggestions,
                ),
                status: Post::STATUS_DRAFT,
                visibility: $visibility,
                publishedAt: null,
                meta: $meta,
                tagIds: $tagIds,
            ));
        } else {
            $post = $this->createPost->handle(new CreatePostCommandData(
                authorUserId: $authorUserId,
                categoryId: $categoryId,
                featuredMediaId: $featuredMediaId,
                title: $result->title,
                slug: $result->slug,
                shortDescription: $result->shortDescription,
                description: $result->description,
                fullArticleHtml: $result->fullArticleHtml,
                fullArticleDelta: $result->fullArticleDelta,
                faq: array_map(
                    static fn (array $faq): array => [
                        'question' => $faq['question'],
                        'answer' => $faq['answer_markdown'],
                    ],
                    $result->faqSuggestions,
                ),
                status: Post::STATUS_DRAFT,
                visibility: $visibility,
                publishedAt: null,
                meta: $meta,
                tagIds: $tagIds,
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

        $this->markTopicUsedById($contentTopicId);

        return $this->assessPostOriginality->handle($post);
    }

    /**
     * @param  array<string, mixed>|null  $existingMeta
     * @param  array<string, mixed>  $metadata
     * @param  list<string>  $secondaryKeywords
     * @return array<string, mixed>
     */
    private function buildMeta(
        mixed $existingMeta,
        int $contentTopicId,
        ?string $primaryKeyword,
        array $secondaryKeywords,
        ?string $searchIntent,
        BlogDraftResult $result,
        array $metadata,
    ): array {
        $meta = array_merge(is_array($existingMeta) ? $existingMeta : [], [
            'source_content_topic_id' => $contentTopicId,
            'ai_job_id' => $this->nullablePositiveInt($metadata['ai_job_id'] ?? null, 'ai_job_id'),
            'primary_keyword' => $primaryKeyword,
            'secondary_keywords' => $secondaryKeywords,
            'search_intent' => $searchIntent,
            'html_body' => $result->fullArticleHtml,
            'quill_delta' => $result->fullArticleDelta,
            'faq_suggestions' => $result->faqSuggestions,
            'suggested_tags' => $result->suggestedTags,
            'image_placement_notes' => $result->imagePlacementNotes,
            'alt_text_suggestions' => $result->altTextSuggestions,
            'generated_by' => 'BlogWriterAgent',
        ]);

        return $meta;
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
            $lookup[mb_strtolower($tag->name)] = (int) $tag->id;
            $lookup[mb_strtolower($tag->slug)] = (int) $tag->id;
        }

        $tagIds = [];

        foreach ($suggestedTags as $suggestedTag) {
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

    private function markTopicUsedById(int $contentTopicId): void
    {
        $topic = ContentTopic::query()->find($contentTopicId);

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
}
