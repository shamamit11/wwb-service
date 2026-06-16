<?php

namespace Tests\Feature;

use App\Enums\ContentBlockType;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostBlock;
use App\Models\Template;
use App\Models\TemplateBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PostBlockModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_blocks_table_matches_the_documented_design_baseline(): void
    {
        $this->assertTrue(Schema::hasTable('post_blocks'));
        $this->assertTrue(Schema::hasColumns('post_blocks', [
            'id',
            'post_id',
            'block_key',
            'block_type',
            'sort_order',
            'content_markdown',
            'content_html_cache',
            'plain_text_cache',
            'settings',
            'source_template_block_id',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_supported_block_types_are_defined_centrally(): void
    {
        $this->assertSame([
            'heading',
            'paragraph',
            'image',
            'quote',
            'list',
            'code',
            'faq',
            'callout',
        ], ContentBlockType::values());
    }

    public function test_post_blocks_persist_with_deterministic_ordering_and_template_traceability(): void
    {
        $author = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => 'Architecture',
            'slug' => 'architecture',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);
        $template = Template::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => 'Tutorial',
            'slug' => 'tutorial',
            'template_type' => 'tutorial',
            'description' => null,
            'status' => Template::STATUS_ACTIVE,
            'default_excerpt_prompt' => null,
            'default_meta' => null,
        ]);
        $templateBlock = TemplateBlock::query()->create([
            'template_id' => $template->id,
            'block_type' => ContentBlockType::HEADING->value,
            'sort_order' => 1,
            'label' => 'Title',
            'default_markdown' => '# {{title}}',
            'settings' => ['level' => 1],
            'is_required' => true,
        ]);
        $post = Post::query()->create([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'template_id' => $template->id,
            'featured_media_id' => null,
            'title' => 'How AI Agent Memory Works',
            'slug' => 'how-ai-agent-memory-works',
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
        ]);

        PostBlock::query()->create([
            'post_id' => $post->id,
            'block_type' => ContentBlockType::PARAGRAPH->value,
            'sort_order' => 2,
            'content_markdown' => 'Intro content',
            'content_html_cache' => '<p>Intro content</p>',
            'plain_text_cache' => 'Intro content',
            'settings' => ['markdown' => true],
            'source_template_block_id' => null,
        ]);

        $headingBlock = PostBlock::query()->create([
            'post_id' => $post->id,
            'block_type' => ContentBlockType::HEADING->value,
            'sort_order' => 1,
            'content_markdown' => '# How AI Agent Memory Works',
            'content_html_cache' => '<h1>How AI Agent Memory Works</h1>',
            'plain_text_cache' => 'How AI Agent Memory Works',
            'settings' => ['level' => 1],
            'source_template_block_id' => $templateBlock->id,
        ]);

        $orderedBlocks = $post->fresh()->blocks;

        $this->assertCount(2, $orderedBlocks);
        $this->assertSame(
            [ContentBlockType::HEADING->value, ContentBlockType::PARAGRAPH->value],
            $orderedBlocks->pluck('block_type')->all(),
        );
        $this->assertSame($templateBlock->id, $headingBlock->sourceTemplateBlock?->id);
        $this->assertSame($headingBlock->id, $templateBlock->sourcedPostBlocks->sole()->id);
        $this->assertSame(26, strlen($headingBlock->block_key));
        $this->assertDatabaseHas('post_blocks', [
            'id' => $headingBlock->id,
            'post_id' => $post->id,
            'block_type' => ContentBlockType::HEADING->value,
            'sort_order' => 1,
            'source_template_block_id' => $templateBlock->id,
        ]);
    }
}
