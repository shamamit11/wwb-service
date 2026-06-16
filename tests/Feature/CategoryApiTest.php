<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_category_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/categories')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_crud_categories(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/categories', [
            'name' => 'AI Agents',
            'description' => 'Technical content about agents.',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'AI Agents')
            ->assertJsonPath('data.slug', 'ai-agents')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.sort_order', 10);

        $categoryId = (int) $createResponse->json('data.id');

        $this->withToken($token)->getJson('/api/v1/admin/categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $categoryId);

        $this->withToken($token)->getJson("/api/v1/admin/categories/{$categoryId}")
            ->assertOk()
            ->assertJsonPath('data.slug', 'ai-agents');

        $this->withToken($token)->putJson("/api/v1/admin/categories/{$categoryId}", [
            'name' => 'AI Systems',
            'slug' => 'ai-systems',
            'description' => 'Updated description.',
            'parent_id' => null,
            'is_active' => false,
            'sort_order' => 2,
        ])->assertOk()
            ->assertJsonPath('data.name', 'AI Systems')
            ->assertJsonPath('data.slug', 'ai-systems')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)->deleteJson("/api/v1/admin/categories/{$categoryId}")
            ->assertNoContent();

        $this->assertSoftDeleted('categories', [
            'id' => $categoryId,
        ]);
    }

    public function test_public_category_endpoints_only_expose_active_categories(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $activeCategory = Category::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'name' => 'Active Category',
            'slug' => 'active-category',
            'description' => 'Visible category.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Category::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'name' => 'Inactive Category',
            'slug' => 'inactive-category',
            'description' => 'Hidden category.',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $activeCategory->id)
            ->assertJsonPath('data.0.slug', 'active-category');

        $this->getJson('/api/v1/categories/active-category')
            ->assertOk()
            ->assertJsonPath('data.id', $activeCategory->id);

        $this->getJson('/api/v1/categories/inactive-category')
            ->assertStatus(404)
            ->assertJsonPath('error_code', 'NOT_FOUND');
    }

    public function test_admin_category_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/categories', [
            'name' => '',
            'slug' => str_repeat('a', 161),
            'sort_order' => -1,
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['name', 'slug', 'sort_order'],
                'meta' => ['request_id'],
            ]);
    }
}
