<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Modules\Seo\Services\ListRssFeedEntriesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RssFeedApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://service.widewebblog.test');
        config()->set('app.frontend_url', 'https://www.widewebblog.com');
    }

    public function test_admin_rss_feed_route_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/feeds/rss')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_rss_feed_service_and_endpoint_return_latest_published_articles_in_feed_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $category = $this->createCategory($admin, 'AI Agents', 'ai-agents');

        $olderPublished = $this->createPost($admin, $category, [
            'title' => 'Older Published',
            'slug' => 'older-published',
            'excerpt' => 'Older feed summary',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-10 08:00:00',
            'updated_at' => '2026-06-11 10:00:00',
        ]);

        $newerPublished = $this->createPost($admin, $category, [
            'title' => 'Newer Published',
            'slug' => 'newer-published',
            'excerpt' => 'Newer feed summary',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-12 09:30:00',
            'updated_at' => '2026-06-13 12:15:00',
        ]);

        $newerPublished->seo()->create([
            'meta_title' => 'Newer Published SEO',
            'meta_description' => 'SEO description for the latest article',
            'canonical_url' => 'https://service.widewebblog.test/newer-published/',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

        $this->createPost($admin, $category, [
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'excerpt' => 'Draft summary',
            'status' => Post::STATUS_DRAFT,
            'visibility' => Post::VISIBILITY_PUBLIC,
            'published_at' => null,
        ]);

        $this->createPost($admin, $category, [
            'title' => 'Internal Published',
            'slug' => 'internal-published',
            'excerpt' => 'Internal summary',
            'status' => Post::STATUS_PUBLISHED,
            'visibility' => Post::VISIBILITY_INTERNAL,
            'published_at' => '2026-06-09 08:00:00',
        ]);

        $serviceEntries = app(ListRssFeedEntriesService::class)->handle();

        $this->assertCount(2, $serviceEntries);
        $this->assertSame([$newerPublished->id, $olderPublished->id], $serviceEntries->pluck('id')->all());

        $this->withToken($token)->getJson('/api/v1/admin/feeds/rss')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newerPublished->id)
            ->assertJsonPath('data.0.type', 'post')
            ->assertJsonPath('data.0.slug', 'newer-published')
            ->assertJsonPath('data.0.title', 'Newer Published')
            ->assertJsonPath('data.0.description', 'SEO description for the latest article')
            ->assertJsonPath('data.0.link', 'https://www.widewebblog.com/newer-published/')
            ->assertJsonPath('data.0.published_at', '2026-06-12T09:30:00.000000Z')
            ->assertJsonPath('data.0.last_modified_at', $newerPublished->fresh()->updated_at?->toISOString())
            ->assertJsonPath('data.0.author.id', $admin->id)
            ->assertJsonPath('data.0.author.name', $admin->name)
            ->assertJsonPath('data.0.category.slug', 'ai-agents')
            ->assertJsonPath('data.1.id', $olderPublished->id)
            ->assertJsonPath('data.1.description', 'Older feed summary');
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
