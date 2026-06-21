<?php

namespace App\Modules\Posts\Services;

use App\AI\Agents\BlogWriterAgent;
use App\AI\DTO\BlogDraftInput;
use App\Models\ContentTopic;
use App\Models\Post;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use App\Modules\Posts\Data\GeneratedBlogDraftData;
use App\Modules\Posts\Repositories\PostRepository;

class GenerateBlogDraftFromTopicService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly BlogWriterAgent $agent,
        private readonly KnowledgeContextService $knowledgeContext,
    ) {}

    public function handle(
        ContentTopic $topic,
        int $authorUserId,
        int $categoryId,
        ?int $featuredMediaId = null,
        string $visibility = Post::VISIBILITY_PUBLIC,
        ?int $aiJobId = null,
    ): GeneratedBlogDraftData {
        $existing = $this->posts->findBySourceContentTopicId((int) $topic->id);
        $topic->loadMissing('category');

        if ($existing instanceof Post) {
            return new GeneratedBlogDraftData($existing, false);
        }

        if (! $topic->canGenerateDraft()) {
            throw new \RuntimeException("Blog draft can only be generated from approved topics. Current status is [{$topic->status}].");
        }

        $result = $this->agent->run(new BlogDraftInput(
            contentTopicId: (int) $topic->id,
            title: $topic->title,
            slug: $topic->slug,
            primaryKeyword: $topic->primary_keyword,
            secondaryKeywords: $topic->secondary_keywords ?? [],
            searchIntent: $topic->search_intent,
            introAngle: $topic->difficulty_note,
            targetAudience: null,
            faqSuggestions: [],
            knowledgeBaseContext: $this->knowledgeContext->forPrompt(new KnowledgeContextQueryData(
                subject: $topic->title,
                keywords: array_values(array_filter([
                    $topic->primary_keyword,
                    ...($topic->secondary_keywords ?? []),
                    $topic->category?->name,
                    $topic->category?->slug,
                    $topic->cluster,
                ], static fn (mixed $value): bool => is_string($value) && $value !== '')),
                metadataFilters: array_filter([
                    'category_slugs' => $topic->category?->slug !== null ? [$topic->category->slug] : null,
                    'clusters' => [$topic->cluster],
                ], static fn (mixed $value): bool => $value !== null),
                maxEntries: 8,
                maxEntryCharacters: 340,
                maxTotalCharacters: 2200,
            )),
            metadata: array_filter([
                'ai_job_id' => $aiJobId,
                'author_user_id' => $authorUserId,
                'category_id' => $categoryId,
                'featured_media_id' => $featuredMediaId,
                'visibility' => $visibility,
            ], static fn (mixed $value): bool => $value !== null),
        ));

        if (! $result->isSuccessful()) {
            throw new \RuntimeException($result->error?->message ?? 'Blog writer agent failed to generate a draft post.');
        }

        $postId = $result->metadata['post_id'] ?? null;
        $post = is_int($postId)
            ? $this->posts->findById($postId)
            : $this->posts->findBySourceContentTopicId((int) $topic->id);

        if (! $post instanceof Post) {
            throw new \RuntimeException('Blog writer agent did not persist a draft post.');
        }

        return new GeneratedBlogDraftData($post, true);
    }
}
