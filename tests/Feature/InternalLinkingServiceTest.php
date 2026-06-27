<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\KnowledgeBaseEntry;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Modules\Seo\Data\InternalLinkContextData;
use App\Modules\Seo\Services\FindRelatedContentService;
use App\Modules\Seo\Services\SuggestInternalLinksService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalLinkingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://widewebblog.test');
        config()->set('app.frontend_url', 'https://www.widewebblog.com');
    }

    public function test_related_content_service_returns_relevant_published_posts_and_active_knowledge_base_entries(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $otherCategory = $this->createCategory($author, 'SEO', 'seo');
        $memoryTag = $this->createTag('Memory', 'memory');
        $agentsTag = $this->createTag('Agents', 'agents');
        $seoTag = $this->createTag('SEO', 'seo');

        $source = $this->createPost($author, $category, [
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
            'short_description' => 'A practical guide to AI agent memory.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 09:30:00',
        ], [$memoryTag, $agentsTag]);
        $source->seo()->create([
            'robots_index' => true,
            'robots_follow' => true,
            'focus_keyword' => 'ai agent memory',
        ]);

        $relatedPost = $this->createPost($author, $category, [
            'title' => 'AI Agent Memory Patterns',
            'slug' => 'ai-agent-memory-patterns',
            'short_description' => 'Patterns for retaining useful agent context.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-10 08:00:00',
        ], [$memoryTag]);
        $relatedPost->seo()->create([
            'robots_index' => true,
            'robots_follow' => true,
            'focus_keyword' => 'agent memory patterns',
        ]);

        $this->createPost($author, $otherCategory, [
            'title' => 'Private SEO Notes',
            'slug' => 'private-seo-notes',
            'short_description' => 'Not public.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PRIVATE,
            'published_at' => '2026-06-09 08:00:00',
        ], [$seoTag]);

        $knowledgeBaseEntry = KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'title' => 'Agent Memory Research Notes',
            'slug' => 'agent-memory-research-notes',
            'entry_type' => KnowledgeBaseEntry::TYPE_RESEARCH,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Research notes about agent memory and context retention.',
            'content_markdown' => 'AI agents need memory retrieval strategies.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => [
                'tags' => ['memory', 'agents'],
                KnowledgeBaseEntry::LINK_HOOKS_KEY => [
                    'posts' => [[
                        'id' => $source->id,
                        'title' => $source->title,
                        'slug' => $source->slug,
                    ]],
                ],
            ],
        ]);

        KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'title' => 'Archived Notes',
            'slug' => 'archived-notes',
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => KnowledgeBaseEntry::STATUS_ARCHIVED,
            'summary' => 'Should not appear.',
            'content_markdown' => 'Old content.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ]);

        $results = app(FindRelatedContentService::class)->handleForPost($source, 5);

        $this->assertCount(2, $results);
        $this->assertSame('post', $results[0]->contentType);
        $this->assertSame($relatedPost->id, $results[0]->id);
        $this->assertSame('knowledge_base_entry', $results[1]->contentType);
        $this->assertSame($knowledgeBaseEntry->id, $results[1]->id);
        $this->assertContains('memory', $results[0]->matchedTerms);
        $this->assertSame('https://www.widewebblog.com/articles/ai-agent-memory-patterns/', $results[0]->url);
        $this->assertSame('https://www.widewebblog.com/knowledge-base/agent-memory-research-notes/', $results[1]->url);
    }

    public function test_internal_link_suggestion_service_supports_draft_context(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = $this->createCategory($author, 'AI Agents', 'ai-agents');
        $memoryTag = $this->createTag('Memory', 'memory');

        $publishedPost = $this->createPost($author, $category, [
            'title' => 'Memory Retrieval for AI Agents',
            'slug' => 'memory-retrieval-for-ai-agents',
            'short_description' => 'How retrieval helps memory-driven agents.',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-10 08:00:00',
        ], [$memoryTag]);

        $publishedPost->seo()->create([
            'robots_index' => true,
            'robots_follow' => true,
            'focus_keyword' => 'ai agent retrieval',
        ]);

        $entry = KnowledgeBaseEntry::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'title' => 'Retrieval Architecture Reference',
            'slug' => 'retrieval-architecture-reference',
            'entry_type' => KnowledgeBaseEntry::TYPE_ARCHITECTURE,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Reference notes on retrieval architecture.',
            'content_markdown' => 'Agent retrieval patterns support long-term memory.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => [
                'tags' => ['memory', 'retrieval'],
            ],
        ]);

        $context = new InternalLinkContextData(
            title: 'Designing AI Agent Retrieval Systems',
            excerpt: 'A draft about retrieval patterns for agent memory.',
            categoryId: $category->id,
            tagNames: ['Memory', 'Retrieval'],
            focusKeyword: 'agent retrieval',
        );

        $suggestions = app(SuggestInternalLinksService::class)->handleForContext($context, 5);

        $this->assertCount(2, $suggestions);
        $this->assertSame($publishedPost->id, $suggestions[0]->id);
        $this->assertSame('post', $suggestions[0]->contentType);
        $this->assertContains($suggestions[0]->anchorText, $suggestions[0]->matchedTerms);
        $this->assertSame('Published content shares overlapping category, tags, or editorial keywords.', $suggestions[0]->reason);
        $this->assertSame($entry->id, $suggestions[1]->id);
        $this->assertSame('knowledge_base_entry', $suggestions[1]->contentType);
        $this->assertSame('Knowledge base context overlaps with the draft or source post.', $suggestions[1]->reason);
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
            'featured_media_id' => null,
            'title' => 'Sample Post',
            'slug' => 'sample-post',
            'short_description' => null,
            'description' => null,
            'full_article_html' => '<h1>Sample Post</h1><p>Sample body.</p>',
            'full_article_delta' => null,
            'faq' => [],
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
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
