<?php

namespace Tests\Feature;

use App\AI\DTO\BlogDraftResult;
use App\AI\DTO\ContentBriefResult;
use App\AI\DTO\TopicSuggestionData;
use App\AI\Tools\CheckDuplicateTopicTool;
use App\AI\Tools\FindInternalLinksTool;
use App\AI\Tools\SaveContentBriefTool;
use App\AI\Tools\SavePostDraftTool;
use App\AI\Tools\SaveTopicIdeaTool;
use App\AI\Tools\SearchExistingPostsTool;
use App\Jobs\AI\GenerateContentBriefJob;
use App\Models\Category;
use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Models\KnowledgeBaseEntry;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class InternalAiToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://widewebblog.test');
    }

    public function test_search_existing_posts_tool_returns_only_published_posts(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $memoryTag = $this->createTag('Memory', 'memory');

        $published = $this->createPost($author, $category, [
            'title' => 'Agent Memory Retrieval Patterns',
            'slug' => 'agent-memory-retrieval-patterns',
            'excerpt' => 'Patterns for retrieval and memory workflows.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-10 08:00:00',
        ], [$memoryTag]);
        $published->seo()->create([
            'robots_index' => true,
            'robots_follow' => true,
            'focus_keyword' => 'agent memory retrieval',
        ]);

        $this->createPost($author, $category, [
            'title' => 'Agent Memory Draft',
            'slug' => 'agent-memory-draft',
            'status' => Post::STATUS_DRAFT,
        ], [$memoryTag]);

        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'title' => 'Agent Memory Research',
            'slug' => 'agent-memory-research',
            'entry_type' => KnowledgeBaseEntry::TYPE_RESEARCH,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Knowledge entry that should not be returned by the posts tool.',
            'content_markdown' => 'Research notes about agent memory retrieval.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => ['tags' => ['memory']],
        ]);

        $results = app(SearchExistingPostsTool::class)->search(
            title: 'Designing Agent Memory Retrieval',
            primaryKeyword: 'agent memory retrieval',
            secondaryKeywords: ['memory'],
            excerpt: 'A draft about retrieval patterns.',
        );

        $this->assertCount(1, $results);
        $this->assertSame('post', $results[0]['contentType']);
        $this->assertSame($published->id, $results[0]['id']);
    }

    public function test_check_duplicate_topic_tool_detects_existing_topics_and_posts(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');

        ContentTopic::query()->create([
            'title' => 'Prompt Versioning for Teams',
            'slug' => 'prompt-versioning-for-teams',
            'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
            'primary_keyword' => 'prompt versioning',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '80.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_SUGGESTED,
            'notes' => null,
        ]);

        $this->createPost($author, $category, [
            'title' => 'Agent Memory Patterns',
            'slug' => 'agent-memory-patterns',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 09:30:00',
        ]);

        $topicDuplicate = app(CheckDuplicateTopicTool::class)->check(
            title: 'Prompt Versioning for Teams',
            cluster: ContentTopic::CLUSTER_AI_TOOLS,
            primaryKeyword: 'prompt versioning',
        );

        $postDuplicate = app(CheckDuplicateTopicTool::class)->check(
            title: 'Agent Memory Patterns',
            cluster: ContentTopic::CLUSTER_DEVELOPER_AI,
            slug: 'agent-memory-patterns',
        );

        $this->assertTrue($topicDuplicate['is_duplicate']);
        $this->assertSame(['content_topic'], $topicDuplicate['matches']);
        $this->assertTrue($postDuplicate['is_duplicate']);
        $this->assertSame(['post'], $postDuplicate['matches']);
    }

    public function test_save_topic_idea_tool_auto_approves_high_priority_topics_via_service_rules(): void
    {
        Queue::fake();

        $saved = app(SaveTopicIdeaTool::class)->save(new TopicSuggestionData(
            title: 'AI Content Audit Checklists',
            slug: 'ai-content-audit-checklists',
            cluster: ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            primaryKeyword: 'ai content audit checklist',
            secondaryKeywords: ['editorial ops'],
            searchIntent: 'informational',
            priorityScore: '91.50',
            difficultyNote: 'Specific operational angle.',
            summary: 'A checklist-driven topic for editorial systems.',
        ), 'Editorial leads');

        $this->assertSame(ContentTopic::SOURCE_AI_SUGGESTED, $saved->source);
        $this->assertSame(ContentTopic::STATUS_APPROVED, $saved->status);
        $this->assertStringContainsString('AI summary: A checklist-driven topic for editorial systems.', (string) $saved->notes);
        $this->assertStringContainsString('Audience: Editorial leads', (string) $saved->notes);
        Queue::assertPushed(GenerateContentBriefJob::class, 1);
    }

    public function test_save_content_brief_tool_updates_existing_brief_as_draft(): void
    {
        $topic = ContentTopic::query()->create([
            'title' => 'AI Review Checklists',
            'slug' => 'ai-review-checklists',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai review checklist',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'priority_score' => '78.00',
            'difficulty_note' => null,
            'source' => ContentTopic::SOURCE_MANUAL,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => null,
            'approved_at' => now(),
        ]);

        $existing = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'Old Brief',
            'slug' => 'old-brief',
            'meta_title' => null,
            'meta_description' => null,
            'primary_keyword' => 'old brief',
            'secondary_keywords' => [],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Old', 'purpose' => 'Old']],
            'headings' => ['Old'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_USED,
        ]);

        $brief = app(SaveContentBriefTool::class)->save(
            contentTopicId: (int) $topic->id,
            primaryKeyword: 'ai review checklist',
            secondaryKeywords: ['editorial ops'],
            searchIntent: 'informational',
            result: new ContentBriefResult(
                recommendedTitle: 'AI Review Checklists for Editorial Teams',
                slug: 'ai-review-checklists-for-editorial-teams',
                metaTitle: 'AI Review Checklists for Editorial Teams',
                metaDescription: 'Structured brief for editorial review workflows.',
                outline: [['heading' => 'Why review checklists matter', 'purpose' => 'Frame the topic']],
                headingStructure: ['Why review checklists matter'],
                faqSuggestions: [['question' => 'What belongs in a review checklist?', 'answer_focus' => 'Review criteria']],
                internalLinkSuggestions: [['title' => 'Editorial QA', 'url' => '/knowledge/editorial-qa']],
                imageIdeas: ['Workflow diagram'],
                altTextSuggestions: ['Workflow diagram for editorial review'],
            ),
        );

        $this->assertSame($existing->id, $brief->id);
        $this->assertSame(ContentBrief::STATUS_DRAFT, $brief->status);
        $this->assertSame('AI Review Checklists for Editorial Teams', $brief->title);
        $this->assertSame('ai-review-checklists-for-editorial-teams', $brief->slug);
    }

    public function test_save_post_draft_tool_creates_draft_and_does_not_publish(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Workflows', 'ai-workflows');
        $tag = $this->createTag('Editorial Ops', 'editorial-ops');

        $topic = ContentTopic::query()->create([
            'title' => 'AI Editorial Review Checklists',
            'slug' => 'ai-editorial-review-checklists',
            'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
            'primary_keyword' => 'ai editorial review checklist',
            'secondary_keywords' => ['editorial ops'],
            'search_intent' => 'informational',
            'priority_score' => '92.00',
            'difficulty_note' => 'Strong operational angle.',
            'source' => ContentTopic::SOURCE_AI_SUGGESTED,
            'status' => ContentTopic::STATUS_APPROVED,
            'notes' => null,
            'approved_at' => now(),
        ]);

        $brief = ContentBrief::query()->create([
            'content_topic_id' => $topic->id,
            'title' => 'AI Editorial Review Checklists for Content Teams',
            'slug' => 'ai-editorial-review-checklists-for-content-teams',
            'meta_title' => null,
            'meta_description' => null,
            'primary_keyword' => 'ai editorial review checklist',
            'secondary_keywords' => ['editorial ops'],
            'search_intent' => 'informational',
            'outline' => [['heading' => 'Intro', 'purpose' => 'Frame the problem']],
            'headings' => ['Intro'],
            'faq_suggestions' => [],
            'internal_link_suggestions' => [],
            'image_suggestions' => [],
            'status' => ContentBrief::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $post = app(SavePostDraftTool::class)->save(
            contentBriefId: (int) $brief->id,
            contentTopicId: (int) $topic->id,
            primaryKeyword: 'ai editorial review checklist',
            secondaryKeywords: ['editorial ops'],
            searchIntent: 'informational',
            result: new BlogDraftResult(
                title: 'AI Editorial Review Checklists for Content Teams',
                slug: 'ai-editorial-review-checklists-for-content-teams',
                markdownBody: "# AI Editorial Review Checklists for Content Teams\n\nUse review gates before publishing.",
                excerpt: 'A practical draft for editorial review workflows.',
                contentBlocks: [
                    [
                        'block_type' => 'heading',
                        'sort_order' => 1,
                        'content' => ['text' => 'AI Editorial Review Checklists for Content Teams', 'level' => 1],
                    ],
                    [
                        'block_type' => 'paragraph',
                        'sort_order' => 2,
                        'content' => ['markdown' => 'Use review gates before publishing.'],
                    ],
                ],
                seoTitle: 'AI Editorial Review Checklists for Content Teams',
                metaDescription: 'A practical draft for editorial review workflows.',
                faqSuggestions: [['question' => 'What belongs in a review checklist?', 'answer_markdown' => 'Accuracy and brand checks.']],
                suggestedTags: [$tag->name, 'Unknown Tag'],
                imagePlacementNotes: ['Add a workflow diagram after the intro.'],
                altTextSuggestions: ['Workflow diagram showing review stages'],
            ),
            metadata: [
                'author_user_id' => $author->id,
                'category_id' => $category->id,
                'visibility' => Post::VISIBILITY_PUBLIC,
            ],
        );

        $this->assertSame(Post::STATUS_DRAFT, $post->status);
        $this->assertNull($post->published_at);
        $this->assertSame([$tag->id], $post->tags->modelKeys());
        $this->assertSame('BlogWriterAgent', $post->meta['generated_by']);
        $this->assertSame(ContentBrief::STATUS_USED, $brief->fresh()->status);
        $this->assertSame(ContentTopic::STATUS_USED, $topic->fresh()->status);
    }

    public function test_find_internal_links_tool_returns_published_link_candidates(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $memoryTag = $this->createTag('Memory', 'memory');

        $published = $this->createPost($author, $category, [
            'title' => 'Memory Retrieval for AI Agents',
            'slug' => 'memory-retrieval-for-ai-agents',
            'excerpt' => 'How retrieval helps memory-driven agents.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-10 08:00:00',
        ], [$memoryTag]);
        $published->seo()->create([
            'robots_index' => true,
            'robots_follow' => true,
            'focus_keyword' => 'ai agent retrieval',
        ]);

        $this->createPost($author, $category, [
            'title' => 'Private Retrieval Notes',
            'slug' => 'private-retrieval-notes',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PRIVATE,
            'published_at' => '2026-06-09 08:00:00',
        ], [$memoryTag]);

        $suggestions = app(FindInternalLinksTool::class)->suggest(
            title: 'Designing AI Agent Retrieval Systems',
            primaryKeyword: 'agent retrieval',
            secondaryKeywords: ['Memory'],
            excerpt: 'A draft about retrieval patterns for agent memory.',
        );

        $this->assertNotEmpty($suggestions);
        $this->assertSame('post', $suggestions[0]['contentType']);
        $this->assertSame($published->id, $suggestions[0]['id']);
        $this->assertSame('Published content shares overlapping category, tags, or editorial keywords.', $suggestions[0]['reason']);
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

    /**
     * @param  array<string, mixed>  $overrides
     * @param  list<Tag>  $tags
     */
    private function createPost(User $author, Category $category, array $overrides = [], array $tags = []): Post
    {
        $post = Post::query()->create(array_merge([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'template_id' => null,
            'featured_media_id' => null,
            'title' => 'Sample Post',
            'slug' => 'sample-post-'.str()->lower(str()->random(6)),
            'excerpt' => null,
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'scheduled_for' => null,
            'content_version' => 1,
            'reading_time_minutes' => null,
            'word_count' => null,
            'is_featured' => false,
            'meta' => null,
        ], $overrides));

        $post->tags()->sync(array_map(static fn (Tag $tag): int => $tag->id, $tags));

        return $post->load(['tags', 'category', 'seo']);
    }

    private function createTag(string $name, string $slug): Tag
    {
        return Tag::query()->create([
            'name' => $name,
            'slug' => $slug,
            'description' => null,
            'is_active' => true,
        ]);
    }
}
