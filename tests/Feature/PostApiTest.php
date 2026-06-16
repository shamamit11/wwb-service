<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');
        config()->set('filesystems.disks.r2.bucket', 'wwb-media');
    }

    public function test_admin_post_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/posts')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_crud_posts_with_structured_blocks(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $template = $this->createTemplate($admin, 'Tutorial', 'tutorial');
        $media = $this->createMedia($admin, 'featured.webp');
        $tagOne = $this->createTag('Memory', 'memory');
        $tagTwo = $this->createTag('Architecture', 'architecture');

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/posts', [
            'title' => 'How AI Agent Memory Works',
            'excerpt' => 'A practical look at short-term and long-term agent memory.',
            'category_id' => $category->id,
            'template_id' => $template->id,
            'featured_media_id' => $media->id,
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'content_version' => 1,
            'reading_time_minutes' => 8,
            'word_count' => 1200,
            'is_featured' => true,
            'meta' => [
                'seo' => ['title' => 'AI Agent Memory'],
            ],
            'tag_ids' => [$tagOne->id, $tagTwo->id],
            'blocks' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'content' => [
                        'text' => 'How AI Agent Memory Works',
                        'level' => 1,
                    ],
                ],
                [
                    'block_type' => 'paragraph',
                    'sort_order' => 2,
                    'content' => [
                        'markdown' => 'Memory patterns determine how agents retain useful context over time.',
                    ],
                ],
            ],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'How AI Agent Memory Works')
            ->assertJsonPath('data.slug', 'how-ai-agent-memory-works')
            ->assertJsonPath('data.status', Post::STATUS_DRAFT)
            ->assertJsonPath('data.visibility', Post::VISIBILITY_PUBLIC)
            ->assertJsonPath('data.category.slug', 'ai-agents')
            ->assertJsonPath('data.template.slug', 'tutorial')
            ->assertJsonPath('data.featured_media.id', $media->id)
            ->assertJsonPath('data.tags.0.slug', 'memory')
            ->assertJsonPath('data.blocks.0.block_type', 'heading')
            ->assertJsonPath('data.blocks.1.block_type', 'paragraph');

        $postId = (int) $createResponse->json('data.id');

        $this->withToken($token)->getJson('/api/v1/admin/posts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $postId);

        $this->withToken($token)->getJson("/api/v1/admin/posts/{$postId}")
            ->assertOk()
            ->assertJsonPath('data.id', $postId)
            ->assertJsonPath('data.author.id', $admin->id)
            ->assertJsonPath('data.blocks.0.sort_order', 1);

        $replacementTag = $this->createTag('SEO', 'seo');

        $this->withToken($token)->putJson("/api/v1/admin/posts/{$postId}", [
            'title' => 'How AI Agent Memory Systems Work',
            'slug' => 'ai-agent-memory-systems',
            'excerpt' => 'Updated excerpt for the editorial draft.',
            'category_id' => $category->id,
            'template_id' => $template->id,
            'featured_media_id' => $media->id,
            'status' => Post::STATUS_SCHEDULED,
            'visibility' => Post::VISIBILITY_INTERNAL,
            'scheduled_for' => '2026-06-18T09:00:00+00:00',
            'content_version' => 2,
            'reading_time_minutes' => 10,
            'word_count' => 1450,
            'is_featured' => false,
            'meta' => [
                'workflow' => ['state' => 'scheduled'],
            ],
            'tag_ids' => [$replacementTag->id],
            'blocks' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'content' => [
                        'text' => 'How AI Agent Memory Systems Work',
                        'level' => 1,
                    ],
                ],
                [
                    'block_type' => 'faq',
                    'sort_order' => 2,
                    'content' => [
                        'items' => [
                            [
                                'question' => 'What is agent memory?',
                                'answer_markdown' => 'Stored context that helps the system respond consistently.',
                            ],
                        ],
                    ],
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('data.slug', 'ai-agent-memory-systems')
            ->assertJsonPath('data.status', Post::STATUS_SCHEDULED)
            ->assertJsonPath('data.visibility', Post::VISIBILITY_INTERNAL)
            ->assertJsonPath('data.is_featured', false)
            ->assertJsonPath('data.tags.0.slug', 'seo')
            ->assertJsonPath('data.blocks.1.block_type', 'faq');

        $this->withToken($token)->deleteJson("/api/v1/admin/posts/{$postId}")
            ->assertNoContent();

        $this->assertSoftDeleted('posts', [
            'id' => $postId,
        ]);
    }

    public function test_admin_post_list_supports_filters_and_sorting(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAuthor = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $aiCategory = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $seoCategory = $this->createCategory($admin, 'SEO', 'seo');

        $alpha = $this->createPost($admin, $aiCategory, [
            'title' => 'Alpha Systems',
            'slug' => 'alpha-systems',
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'is_featured' => false,
            'published_at' => null,
            'excerpt' => 'Alpha draft',
        ]);

        $beta = $this->createPost($otherAuthor, $seoCategory, [
            'title' => 'Beta Search',
            'slug' => 'beta-search',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'is_featured' => true,
            'published_at' => '2026-06-14 12:00:00',
            'excerpt' => 'Ranking systems',
        ]);

        $gamma = $this->createPost($admin, $aiCategory, [
            'title' => 'Gamma Internal',
            'slug' => 'gamma-internal',
            'status' => Post::STATUS_SCHEDULED,
            'visibility' => Post::VISIBILITY_INTERNAL,
            'is_featured' => true,
            'published_at' => null,
            'excerpt' => 'Internal planning note',
        ]);

        $this->withToken($token)->getJson('/api/v1/admin/posts?status=draft')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $alpha->id);

        $this->withToken($token)->getJson('/api/v1/admin/posts?category_slug=seo')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $beta->id);

        $this->withToken($token)->getJson('/api/v1/admin/posts?is_featured=1')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->withToken($token)->getJson('/api/v1/admin/posts?author_user_id='.$otherAuthor->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $beta->id);

        $this->withToken($token)->getJson('/api/v1/admin/posts?search=Internal')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $gamma->id);

        $this->withToken($token)->getJson('/api/v1/admin/posts?sort=title')
            ->assertOk()
            ->assertJsonPath('data.0.id', $alpha->id)
            ->assertJsonPath('data.1.id', $beta->id)
            ->assertJsonPath('data.2.id', $gamma->id);
    }

    public function test_admin_post_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/posts', [
            'title' => '',
            'category_id' => 999999,
            'status' => 'invalid-status',
            'visibility' => 'unknown',
            'blocks' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'content' => ['text' => 'One', 'level' => 1],
                ],
                [
                    'block_type' => 'paragraph',
                    'sort_order' => 1,
                    'content' => ['markdown' => 'Duplicate sort order'],
                ],
            ],
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['title', 'category_id', 'status', 'visibility', 'blocks.1.sort_order'],
                'meta' => ['request_id'],
            ]);
    }

    public function test_admin_can_publish_schedule_and_unpublish_posts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $post = $this->createPost($admin, $category, [
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
            'scheduled_for' => null,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/posts/{$post->id}/schedule", [
            'scheduled_for' => '2026-06-20T09:00:00+00:00',
        ])->assertOk()
            ->assertJsonPath('data.status', Post::STATUS_SCHEDULED)
            ->assertJsonPath('data.scheduled_for', '2026-06-20T09:00:00.000000Z')
            ->assertJsonPath('data.published_at', null);

        $this->travelTo(now()->setDate(2026, 6, 18)->setTime(11, 45, 0));

        $this->withToken($token)->postJson("/api/v1/admin/posts/{$post->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', Post::STATUS_PUBLISHED)
            ->assertJsonPath('data.scheduled_for', null)
            ->assertJsonPath('data.published_at', '2026-06-18T11:45:00.000000Z');

        $this->withToken($token)->postJson("/api/v1/admin/posts/{$post->id}/unpublish")
            ->assertOk()
            ->assertJsonPath('data.status', Post::STATUS_UNPUBLISHED)
            ->assertJsonPath('data.scheduled_for', null)
            ->assertJsonPath('data.published_at', null);

        $this->travelBack();
    }

    public function test_admin_post_transition_endpoints_block_invalid_state_changes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $publishedPost = $this->createPost($admin, $category, [
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => '2026-06-14 12:00:00',
            'scheduled_for' => null,
        ]);
        $archivedPost = $this->createPost($admin, $category, [
            'title' => 'Archived Post',
            'slug' => 'archived-post',
            'status' => Post::STATUS_ARCHIVED,
            'published_at' => null,
            'scheduled_for' => null,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/posts/{$publishedPost->id}/schedule", [
            'scheduled_for' => '2026-06-20T09:00:00+00:00',
        ])->assertStatus(409)
            ->assertJsonPath('error_code', 'CONFLICT')
            ->assertJsonPath('errors.status.0', Post::STATUS_PUBLISHED)
            ->assertJsonPath('errors.action.0', 'schedule');

        $this->withToken($token)->postJson("/api/v1/admin/posts/{$archivedPost->id}/publish")
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'CONFLICT')
            ->assertJsonPath('errors.status.0', Post::STATUS_ARCHIVED)
            ->assertJsonPath('errors.action.0', 'publish');
    }

    public function test_admin_schedule_endpoint_requires_a_future_timestamp(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');
        $post = $this->createPost($admin, $category);

        $this->withToken($token)->postJson("/api/v1/admin/posts/{$post->id}/schedule", [
            'scheduled_for' => '2026-06-01T09:00:00+00:00',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonPath('errors.scheduled_for.0', 'The scheduled for field must be a date after now.');
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

    private function createTemplate(User $author, string $name, string $slug): Template
    {
        return Template::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => $name,
            'slug' => $slug,
            'template_type' => $slug,
            'description' => null,
            'status' => Template::STATUS_ACTIVE,
            'default_excerpt_prompt' => null,
            'default_meta' => null,
        ]);
    }

    private function createMedia(User $author, string $filename): Media
    {
        return Media::query()->create([
            'uploaded_by_user_id' => $author->id,
            'generated_by_ai_job_id' => null,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'posts/'.$filename,
            'original_filename' => $filename,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 12345,
            'checksum_sha256' => str_repeat('a', 64),
            'width' => 1200,
            'height' => 630,
            'alt_text' => null,
            'caption' => null,
            'source_type' => 'uploaded',
            'source_url' => null,
            'attribution_text' => null,
            'status' => 'ready',
            'metadata' => null,
        ]);
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

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPost(User $author, Category $category, array $overrides = []): Post
    {
        return Post::query()->create(array_merge([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'template_id' => null,
            'featured_media_id' => null,
            'title' => 'Sample Post',
            'slug' => 'sample-post',
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
    }
}
