<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Homepage;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_admin_routes_require_admin_access(): void
    {
        $this->getJson('/api/v1/admin/homepage')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');

        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/homepage')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN');
    }

    public function test_admin_can_fetch_bootstrapped_homepage_defaults(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/homepage')
            ->assertOk()
            ->assertJsonPath('data.hero.title', null)
            ->assertJsonPath('data.featured_editorial.mode', Homepage::SECTION_MODE_AUTOMATIC)
            ->assertJsonPath('data.featured_editorial.post_ids', [])
            ->assertJsonPath('data.guide_section.title', 'Recent Articles')
            ->assertJsonPath('data.guide_section.mode', Homepage::SECTION_MODE_AUTOMATIC)
            ->assertJsonPath('data.topic_section.category_ids', [])
            ->assertJsonPath('data.promo_section.enabled', false)
            ->assertJsonPath('data.newsletter_section.enabled', false)
            ->assertJsonPath('data.seo.meta_title', null)
            ->assertJsonPath('data.updated_by', null);

        $this->assertDatabaseCount('homepages', 1);
        $this->assertDatabaseHas('homepages', [
            'singleton_key' => Homepage::SINGLETON_KEY,
            'updated_by_user_id' => null,
        ]);
    }

    public function test_admin_can_update_homepage_and_preserve_array_ordering(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $payload = [
            'hero' => [
                'eyebrow' => 'Start here',
                'title' => 'Build better internet systems',
                'description' => 'Editorially curated homepage content.',
                'primary_cta_label' => 'Read featured stories',
                'primary_cta_url' => 'https://widewebblog.test/featured',
                'secondary_cta_label' => 'Browse resources',
                'secondary_cta_url' => 'https://widewebblog.test/resources',
                'media_url' => 'https://cdn.widewebblog.test/home/hero.png',
                'media_alt' => 'Homepage hero artwork',
            ],
            'featured_editorial' => [
                'title' => 'Featured editorial',
                'description' => 'Automatically curated featured stories.',
                'limit' => 2,
            ],
            'guide_section' => [
                'title' => 'Recent Articles',
                'description' => 'Automatically selected recent stories.',
                'limit' => 3,
            ],
            'topic_section' => [
                'title' => 'Explore Core Topics',
                'description' => 'Browse every active category automatically.',
            ],
            'promo_section' => [
                'enabled' => true,
                'eyebrow' => 'Resource pack',
                'title' => 'Download the operator kit',
                'description' => 'A promotional section for resource acquisition.',
                'bullet_points' => ['Checklists', 'Benchmarks', 'Field notes'],
                'primary_cta_label' => 'Get the kit',
                'primary_cta_url' => 'https://widewebblog.test/kit',
                'stats' => [
                    ['label' => 'Templates', 'value' => '12'],
                    ['label' => 'Playbooks', 'value' => '8'],
                ],
            ],
            'newsletter_section' => [
                'enabled' => true,
                'title' => 'Get weekly dispatches',
                'description' => 'Editorial updates and new resources.',
            ],
            'seo' => [
                'meta_title' => 'Wide Web Blog | Homepage',
                'meta_description' => 'Homepage metadata for discovery and click-through.',
            ],
        ];

        $this->withToken($token)
            ->putJson('/api/v1/admin/homepage', $payload)
            ->assertOk()
            ->assertJsonPath('data.hero.title', 'Build better internet systems')
            ->assertJsonPath('data.featured_editorial.mode', Homepage::SECTION_MODE_AUTOMATIC)
            ->assertJsonPath('data.featured_editorial.post_ids', [])
            ->assertJsonPath('data.featured_editorial.limit', 2)
            ->assertJsonPath('data.guide_section.mode', Homepage::SECTION_MODE_AUTOMATIC)
            ->assertJsonPath('data.guide_section.title', 'Recent Articles')
            ->assertJsonPath('data.guide_section.post_ids', [])
            ->assertJsonPath('data.guide_section.limit', 3)
            ->assertJsonPath('data.topic_section.title', 'Explore Core Topics')
            ->assertJsonPath('data.topic_section.category_ids', [])
            ->assertJsonPath('data.promo_section.bullet_points.0', 'Checklists')
            ->assertJsonPath('data.promo_section.bullet_points.2', 'Field notes')
            ->assertJsonPath('data.promo_section.stats.0.label', 'Templates')
            ->assertJsonPath('data.promo_section.stats.1.label', 'Playbooks')
            ->assertJsonPath('data.newsletter_section.enabled', true)
            ->assertJsonPath('data.seo.meta_title', 'Wide Web Blog | Homepage')
            ->assertJsonPath('data.updated_by.id', $admin->id);

        $homepage = Homepage::query()->firstOrFail();

        $this->assertSame(Homepage::SECTION_MODE_AUTOMATIC, $homepage->featured_editorial['mode']);
        $this->assertSame([], $homepage->featured_editorial['post_ids']);
        $this->assertSame(2, $homepage->featured_editorial['limit']);
        $this->assertSame('Recent Articles', $homepage->guide_section['title']);
        $this->assertSame([], $homepage->guide_section['post_ids']);
        $this->assertSame(3, $homepage->guide_section['limit']);
        $this->assertSame([], $homepage->topic_section['category_ids']);
        $this->assertSame(['Checklists', 'Benchmarks', 'Field notes'], $homepage->promo_section['bullet_points']);
        $this->assertSame([
            ['label' => 'Templates', 'value' => '12'],
            ['label' => 'Playbooks', 'value' => '8'],
        ], $homepage->promo_section['stats']);
        $this->assertSame($admin->id, $homepage->updated_by_user_id);
    }

    public function test_homepage_update_validates_nested_payloads(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/v1/admin/homepage', [
                'hero' => [
                    'title' => str_repeat('x', 256),
                    'primary_cta_url' => 'not-a-url',
                ],
                'featured_editorial' => [
                    'category_ids' => null,
                    'limit' => 0,
                ],
                'guide_section' => [
                    'category_ids' => ['bad'],
                    'limit' => 30,
                ],
                'topic_section' => [
                    'category_ids' => ['bad'],
                ],
                'promo_section' => [
                    'enabled' => 'yes',
                    'bullet_points' => ['valid', 42],
                    'primary_cta_url' => 'bad-url',
                    'stats' => [
                        ['label' => '', 'value' => 'ok'],
                        ['label' => 'Missing value'],
                    ],
                ],
                'newsletter_section' => [
                    'enabled' => 'maybe',
                ],
                'seo' => [
                    'meta_description' => str_repeat('d', 321),
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => [
                    'hero.title',
                    'hero.primary_cta_url',
                    'featured_editorial.limit',
                    'guide_section.limit',
                    'promo_section.enabled',
                    'promo_section.bullet_points.1',
                    'promo_section.primary_cta_url',
                    'promo_section.stats.0.label',
                    'promo_section.stats.1.value',
                    'newsletter_section.enabled',
                    'seo.meta_description',
                ],
            ]);
    }

    private function createCategory(User $author, string $name, string $slug): Category
    {
        return Category::query()->create([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'name' => $name,
            'slug' => $slug,
            'description' => "{$name} description.",
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function createPost(User $author, Category $category, string $title, string $slug): Post
    {
        return Post::query()->create([
            'author_user_id' => $author->id,
            'category_id' => $category->id,
            'template_id' => null,
            'featured_media_id' => null,
            'title' => $title,
            'slug' => $slug,
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
    }
}
