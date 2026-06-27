<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Modules\Seo\Services\ListSitemapEntriesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://service.widewebblog.test');
        config()->set('app.frontend_url', 'https://www.widewebblog.com');
    }

    public function test_admin_sitemap_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/seo/sitemap')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_sitemap_service_and_endpoint_only_include_published_public_posts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');

        $olderPublished = $this->createPost($admin, $category, [
            'title' => 'Older Published',
            'slug' => 'older-published',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-10 08:00:00',
            'updated_at' => '2026-06-11 10:00:00',
        ])->load('seo');

        $newerPublished = $this->createPost($admin, $category, [
            'title' => 'Newer Published',
            'slug' => 'newer-published',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 09:30:00',
            'updated_at' => '2026-06-13 12:15:00',
        ]);

        $olderPublished->seo()->create([
            'meta_title' => 'Older Published',
            'canonical_url' => 'https://override.example/posts/older-published',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $this->createPost($admin, $category, [
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
        ]);

        $this->createPost($admin, $category, [
            'title' => 'Private Published',
            'slug' => 'private-published',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PRIVATE,
            'published_at' => '2026-06-09 08:00:00',
        ]);

        $this->createPost($admin, $category, [
            'title' => 'Scheduled Post',
            'slug' => 'scheduled-post',
            'status' => Post::STATUS_SCHEDULED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
            'scheduled_for' => '2026-06-20 08:00:00',
        ]);

        $serviceEntries = app(ListSitemapEntriesService::class)->handle();

        $this->assertCount(2, $serviceEntries);
        $this->assertSame([$newerPublished->id, $olderPublished->id], $serviceEntries->pluck('id')->all());

        $this->withToken($token)->getJson('/api/v1/admin/seo/sitemap')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newerPublished->id)
            ->assertJsonPath('data.0.type', 'post')
            ->assertJsonPath('data.0.slug', 'newer-published')
            ->assertJsonPath('data.0.canonical_url', 'https://www.widewebblog.com/articles/newer-published/')
            ->assertJsonPath('data.0.published_at', '2026-06-12T09:30:00.000000Z')
            ->assertJsonPath('data.0.last_modified_at', $newerPublished->updated_at?->toISOString())
            ->assertJsonPath('data.1.id', $olderPublished->id)
            ->assertJsonPath('data.1.canonical_url', 'https://override.example/posts/older-published');
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
     */
    private function createPost(User $author, Category $category, array $overrides = []): Post
    {
        return tap(Post::query()->create(array_merge([
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
        ], $overrides)), function (Post $post) use ($overrides): void {
            if (array_key_exists('updated_at', $overrides)) {
                $post->forceFill(['updated_at' => $overrides['updated_at']])->saveQuietly();
            }
        });
    }
}
