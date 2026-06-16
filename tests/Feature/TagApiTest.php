<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_tag_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/tags')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_crud_tags(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/tags', [
            'name' => 'AI Agents',
            'description' => 'Cross-cutting AI topics.',
            'is_active' => true,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'AI Agents')
            ->assertJsonPath('data.slug', 'ai-agents')
            ->assertJsonPath('data.is_active', true);

        $tagId = (int) $createResponse->json('data.id');

        $this->withToken($token)->getJson('/api/v1/admin/tags')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $tagId);

        $this->withToken($token)->getJson("/api/v1/admin/tags/{$tagId}")
            ->assertOk()
            ->assertJsonPath('data.slug', 'ai-agents');

        $this->withToken($token)->putJson("/api/v1/admin/tags/{$tagId}", [
            'name' => 'Architecture',
            'slug' => 'architecture',
            'description' => 'Updated description.',
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Architecture')
            ->assertJsonPath('data.slug', 'architecture')
            ->assertJsonPath('data.is_active', false);

        $this->withToken($token)->deleteJson("/api/v1/admin/tags/{$tagId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tags', [
            'id' => $tagId,
        ]);
    }

    public function test_admin_tag_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/tags', [
            'name' => '',
            'slug' => str_repeat('a', 141),
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['name', 'slug'],
                'meta' => ['request_id'],
            ]);
    }

    public function test_tag_slugs_are_generated_and_uniquified_service_side(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        Tag::query()->create([
            'name' => 'AI Agents',
            'slug' => 'ai-agents',
            'description' => null,
            'is_active' => true,
        ]);

        $this->withToken($token)->postJson('/api/v1/admin/tags', [
            'name' => 'AI Agents',
            'description' => 'Duplicate name.',
        ])->assertCreated()
            ->assertJsonPath('data.slug', 'ai-agents-2');
    }
}
