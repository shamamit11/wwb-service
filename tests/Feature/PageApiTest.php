<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_page_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/pages')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_crud_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/pages', [
            'title' => 'Privacy Policy',
            'type' => Page::TYPE_LEGAL,
            'status' => Page::STATUS_DRAFT,
            'summary' => 'How Wide Web Blog handles user data.',
            'content_markdown' => '# Privacy Policy',
            'visibility' => Page::VISIBILITY_PUBLIC,
            'meta' => [
                'layout' => 'legal',
            ],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'Privacy Policy')
            ->assertJsonPath('data.slug', 'privacy-policy')
            ->assertJsonPath('data.type', Page::TYPE_LEGAL)
            ->assertJsonPath('data.status', Page::STATUS_DRAFT)
            ->assertJsonPath('data.visibility', Page::VISIBILITY_PUBLIC)
            ->assertJsonPath('data.canonical_url', null)
            ->assertJsonPath('data.created_by.id', $admin->id)
            ->assertJsonPath('data.updated_by', null)
            ->assertJsonPath('data.meta.layout', 'legal');

        $pageId = (int) $createResponse->json('data.id');

        $this->withToken($token)->getJson('/api/v1/admin/pages')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $pageId);

        $this->withToken($token)->getJson("/api/v1/admin/pages/{$pageId}")
            ->assertOk()
            ->assertJsonPath('data.id', $pageId)
            ->assertJsonPath('data.content_markdown', '# Privacy Policy');

        $this->withToken($token)->putJson("/api/v1/admin/pages/{$pageId}", [
            'title' => 'Privacy Policy and Data Use',
            'slug' => 'privacy-and-data-use',
            'type' => Page::TYPE_LEGAL,
            'status' => Page::STATUS_PUBLISHED,
            'summary' => 'Expanded privacy commitments.',
            'content_markdown' => '# Privacy Policy'.PHP_EOL.PHP_EOL.'Updated details.',
            'visibility' => Page::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-17T10:00:00+00:00',
            'meta' => [
                'revision' => 2,
            ],
        ])->assertOk()
            ->assertJsonPath('data.slug', 'privacy-and-data-use')
            ->assertJsonPath('data.status', Page::STATUS_PUBLISHED)
            ->assertJsonPath('data.canonical_url', 'http://wwb-service.test/pages/privacy-and-data-use/')
            ->assertJsonPath('data.updated_by.id', $admin->id)
            ->assertJsonPath('data.meta.revision', 2);

        $this->withToken($token)->deleteJson("/api/v1/admin/pages/{$pageId}")
            ->assertNoContent();

        $this->assertSoftDeleted('pages', [
            'id' => $pageId,
        ]);
    }

    public function test_admin_page_list_supports_filters_and_sorting(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $otherAuthor = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $privacy = $this->createPage($admin, [
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'type' => Page::TYPE_LEGAL,
            'status' => Page::STATUS_PUBLISHED,
            'visibility' => Page::VISIBILITY_PUBLIC,
            'published_at' => '2026-06-15 10:00:00',
            'summary' => 'Legal page',
        ]);

        $faq = $this->createPage($otherAuthor, [
            'title' => 'FAQ',
            'slug' => 'faq',
            'type' => Page::TYPE_FAQ,
            'status' => Page::STATUS_DRAFT,
            'visibility' => Page::VISIBILITY_INTERNAL,
            'summary' => 'Common editorial questions',
        ]);

        $about = $this->createPage($admin, [
            'title' => 'About Wide Web Blog',
            'slug' => 'about-wide-web-blog',
            'type' => Page::TYPE_MARKETING,
            'status' => Page::STATUS_SCHEDULED,
            'visibility' => Page::VISIBILITY_PUBLIC,
            'summary' => 'Brand story',
        ]);

        $this->withToken($token)->getJson('/api/v1/admin/pages?status=published')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $privacy->id);

        $this->withToken($token)->getJson('/api/v1/admin/pages?type=faq')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $faq->id);

        $this->withToken($token)->getJson('/api/v1/admin/pages?visibility=public')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->withToken($token)->getJson('/api/v1/admin/pages?created_by_user_id='.$otherAuthor->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $faq->id);

        $this->withToken($token)->getJson('/api/v1/admin/pages?search=Brand')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $about->id);

        $this->withToken($token)->getJson('/api/v1/admin/pages?sort=title')
            ->assertOk()
            ->assertJsonPath('data.0.id', $about->id)
            ->assertJsonPath('data.1.id', $faq->id)
            ->assertJsonPath('data.2.id', $privacy->id);
    }

    public function test_admin_page_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/pages', [
            'title' => '',
            'type' => 'unknown',
            'status' => 'invalid-status',
            'content_markdown' => '',
            'visibility' => 'secret',
            'meta' => 'not-an-array',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['title', 'type', 'status', 'content_markdown', 'visibility', 'meta'],
                'meta' => ['request_id'],
            ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPage(User $author, array $overrides = []): Page
    {
        return Page::query()->create(array_merge([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => null,
            'title' => 'Sample Page',
            'slug' => 'sample-page',
            'type' => Page::TYPE_STANDARD,
            'status' => Page::STATUS_DRAFT,
            'summary' => null,
            'content_markdown' => 'Sample content.',
            'visibility' => Page::VISIBILITY_PUBLIC,
            'published_at' => null,
            'scheduled_for' => null,
            'meta' => null,
        ], $overrides));
    }
}
