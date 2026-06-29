<?php

namespace Tests\Feature;

use App\AI\DTO\BlogDraftResult;
use App\AI\Tools\SavePostDraftTool;
use App\Http\Resources\Api\V1\PostResource;
use App\Models\Category;
use App\Models\ContentTopic;
use App\Models\Post;
use App\Models\User;
use App\Modules\Posts\Data\PostFiltersData;
use App\Modules\Posts\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostOriginalityReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_post_draft_flags_ai_drafts_with_substantial_overlap(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');
        $existingPost = $this->createPost($author, $category, [
            'title' => 'Existing AI Writing Guide',
            'slug' => 'existing-ai-writing-guide',
            'full_article_html' => '<p>Teams should document editorial standards before introducing AI into article production because clear boundaries reduce duplicated messaging and factual drift across a growing content library.</p><p>Every generated article should include a manual originality review step so editors can inspect repeated claims, recycled structure, and unsupported examples before anything moves toward publication.</p>',
        ]);

        $topic = $this->createApprovedTopic($category, 'AI Writing Guide');

        $post = app(SavePostDraftTool::class)->save(
            contentTopicId: (int) $topic->id,
            primaryKeyword: 'ai writing guide',
            secondaryKeywords: ['editorial review'],
            searchIntent: 'informational',
            result: new BlogDraftResult(
                title: 'How to Build an AI Writing Guide',
                slug: 'how-to-build-an-ai-writing-guide',
                fullArticleHtml: '<p>Teams should document editorial standards before introducing AI into article production because clear boundaries reduce duplicated messaging and factual drift across a growing content library.</p><p>Every generated article should include a manual originality review step so editors can inspect repeated claims, recycled structure, and unsupported examples before anything moves toward publication.</p><p>Use this checkpoint to decide whether the article needs deeper reporting or a full rewrite before an editor continues with it.</p>',
                shortDescription: 'A draft about AI writing guardrails.',
                description: 'A draft about AI writing guardrails.',
            ),
            metadata: [
                'ai_job_id' => 42,
                'author_user_id' => $author->id,
                'category_id' => $category->id,
                'visibility' => Post::VISIBILITY_PUBLIC,
            ],
        );

        $meta = $this->postMeta($post);
        $review = $meta['originality_review'] ?? null;

        $this->assertTrue($meta['needs_originality_review'] ?? false);
        $this->assertIsArray($review);
        $this->assertSame('flagged', $review['status']);
        $this->assertSame(42, $meta['ai_job_id']);
        $this->assertContains($existingPost->id, $review['matched_post_ids']);
        $this->assertGreaterThanOrEqual(2, $review['matched_sentence_count']);
        $this->assertGreaterThan(0.2, $review['overlap_ratio']);
    }

    public function test_save_post_draft_marks_unique_ai_drafts_as_clear(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'Developer AI', 'developer-ai');
        $this->createPost($author, $category, [
            'title' => 'Existing Agent Workflow',
            'slug' => 'existing-agent-workflow',
            'full_article_html' => '<p>Short existing article that should not overlap with the new draft in any meaningful way because the subject and wording remain distinct.</p>',
        ]);

        $topic = $this->createApprovedTopic($category, 'Agent Evaluation Checklist');

        $post = app(SavePostDraftTool::class)->save(
            contentTopicId: (int) $topic->id,
            primaryKeyword: 'agent evaluation checklist',
            secondaryKeywords: ['model review'],
            searchIntent: 'informational',
            result: new BlogDraftResult(
                title: 'Agent Evaluation Checklist',
                slug: 'agent-evaluation-checklist',
                fullArticleHtml: '<p>Evaluation checklists help teams compare models with consistent criteria, but the strongest workflows add notes about failure cases, reviewer confidence, and pending data collection.</p><p>A useful checklist also separates prompt quality from model capability so editors can see whether a weak result came from instructions, missing context, or the model itself.</p>',
                shortDescription: 'A unique draft about evaluating agents.',
                description: 'A unique draft about evaluating agents.',
            ),
            metadata: [
                'author_user_id' => $author->id,
                'category_id' => $category->id,
                'visibility' => Post::VISIBILITY_PUBLIC,
            ],
        );

        $meta = $this->postMeta($post);
        $review = $meta['originality_review'] ?? null;

        $this->assertFalse($meta['needs_originality_review'] ?? true);
        $this->assertIsArray($review);
        $this->assertSame('clear', $review['status']);
        $this->assertSame([], $review['matched_post_ids']);
        $this->assertSame(0, $review['matched_sentence_count']);
    }

    public function test_post_resource_and_repository_expose_originality_review_signal(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'SEO', 'seo');
        $flaggedPost = $this->createPost($author, $category, [
            'title' => 'Flagged Draft',
            'slug' => 'flagged-draft',
            'meta' => [
                'generated_by' => 'BlogWriterAgent',
                'needs_originality_review' => true,
                'originality_review' => [
                    'status' => 'flagged',
                    'matched_post_ids' => [99],
                    'matched_sentence_count' => 2,
                    'total_sentence_count' => 4,
                    'overlap_ratio' => 0.5,
                    'flag_reasons' => [],
                ],
            ],
        ]);
        $clearPost = $this->createPost($author, $category, [
            'title' => 'Clear Draft',
            'slug' => 'clear-draft',
            'meta' => [
                'generated_by' => 'BlogWriterAgent',
                'needs_originality_review' => false,
            ],
        ]);

        $payload = (new PostResource($flaggedPost->load(['author', 'category', 'tags', 'seo'])))->resolve();
        $results = app(PostRepository::class)->searchAdmin(new PostFiltersData(
            needsOriginalityReview: true,
        ));

        $this->assertTrue($payload['needs_originality_review']);
        $this->assertSame('flagged', $payload['originality_review']['status']);
        $this->assertSame([$flaggedPost->id], $results->modelKeys());

        $clearResults = app(PostRepository::class)->searchAdmin(new PostFiltersData(
            needsOriginalityReview: false,
        ));

        $this->assertContains($clearPost->id, $clearResults->modelKeys());
        $this->assertNotContains($flaggedPost->id, $clearResults->modelKeys());
    }

    private function createCategory(User $author, string $name, string $slug): Category
    {
        return Category::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => $name,
            'slug' => $slug,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function createApprovedTopic(Category $category, string $title): ContentTopic
    {
        return ContentTopic::query()->create([
            'category_id' => $category->id,
            'title' => $title,
            'slug' => str($title)->slug()->value(),
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => strtolower($title),
            'secondary_keywords' => [],
            'priority_score' => '90.00',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPost(User $author, Category $category, array $overrides = []): Post
    {
        return Post::query()->create(array_merge([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'featured_media_id' => null,
            'title' => 'Sample Post',
            'slug' => 'sample-post-'.str()->lower(str()->random(8)),
            'short_description' => null,
            'description' => null,
            'full_article_html' => '<p>Sample body for originality review testing.</p>',
            'full_article_delta' => null,
            'faq' => [],
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'meta' => null,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function postMeta(Post $post): array
    {
        $meta = $post->getAttributeValue('meta');

        return is_array($meta) ? $meta : [];
    }
}
