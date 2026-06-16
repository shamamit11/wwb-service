<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_template_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/templates')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_crud_templates_and_generate_preview_payloads(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/templates', [
            'name' => 'Tutorial',
            'template_type' => 'tutorial',
            'description' => 'Structured step-by-step tutorial layout',
            'status' => 'active',
            'default_excerpt_prompt' => 'Summarize the steps and outcomes.',
            'default_meta' => [
                'recommended_sections' => ['introduction', 'steps', 'faq'],
                'seo_rules' => [
                    'preferred_schema' => 'Article',
                ],
            ],
            'blocks' => [
                [
                    'block_type' => 'paragraph',
                    'sort_order' => 2,
                    'label' => 'Introduction',
                    'default_markdown' => 'Introduce {{topic}} with practical context.',
                    'is_required' => true,
                ],
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'label' => 'Title',
                    'default_markdown' => '# {{title}}',
                    'settings' => ['level' => 1],
                    'is_required' => true,
                ],
            ],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'Tutorial')
            ->assertJsonPath('data.slug', 'tutorial')
            ->assertJsonPath('data.template_type', 'tutorial')
            ->assertJsonCount(2, 'data.blocks')
            ->assertJsonPath('data.blocks.0.block_type', 'heading')
            ->assertJsonPath('data.blocks.1.block_type', 'paragraph');

        $templateId = (int) $createResponse->json('data.id');

        $this->withToken($token)->getJson('/api/v1/admin/templates')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $templateId);

        $this->withToken($token)->getJson("/api/v1/admin/templates/{$templateId}")
            ->assertOk()
            ->assertJsonPath('data.default_meta.seo_rules.preferred_schema', 'Article');

        $this->withToken($token)->postJson("/api/v1/admin/templates/{$templateId}/preview", [
            'title' => 'How AI Agent Memory Works',
            'topic' => 'AI agent memory',
        ])->assertOk()
            ->assertJsonPath('data.template.id', $templateId)
            ->assertJsonPath('data.preview.title', 'How AI Agent Memory Works')
            ->assertJsonPath('data.preview.blocks.0.block_type', 'heading')
            ->assertJsonPath('data.preview.blocks.0.content.text', 'How AI Agent Memory Works')
            ->assertJsonPath('data.preview.blocks.1.content.markdown', 'Introduce AI agent memory with practical context.')
            ->assertJsonPath('data.preview.meta.seo_rules.preferred_schema', 'Article');

        $this->withToken($token)->postJson("/api/v1/admin/templates/{$templateId}/seed-post", [
            'title' => 'AI Agent Memory Patterns',
            'topic' => 'AI agent memory',
        ])->assertOk()
            ->assertJsonPath('data.template.id', $templateId)
            ->assertJsonPath('data.post.template_id', $templateId)
            ->assertJsonPath('data.post.status', 'draft')
            ->assertJsonPath('data.post.slug', 'ai-agent-memory-patterns')
            ->assertJsonPath('data.post.blocks.0.block_type', 'heading')
            ->assertJsonPath('data.post.blocks.0.content.text', 'AI Agent Memory Patterns')
            ->assertJsonPath('data.post.meta.recommended_sections.0', 'introduction');

        $this->withToken($token)->putJson("/api/v1/admin/templates/{$templateId}", [
            'name' => 'Comparison',
            'slug' => 'comparison',
            'template_type' => 'comparison',
            'description' => 'Tradeoff-oriented comparison layout',
            'status' => 'draft',
            'default_excerpt_prompt' => 'Summarize the tradeoffs and recommendation.',
            'default_meta' => [
                'recommended_sections' => ['problem', 'option-a', 'option-b', 'recommendation'],
            ],
            'blocks' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'label' => 'Title',
                    'default_markdown' => '# {{title}}',
                    'settings' => ['level' => 1],
                    'is_required' => true,
                ],
                [
                    'block_type' => 'callout',
                    'sort_order' => 2,
                    'label' => 'Decision summary',
                    'default_markdown' => 'Use this block for the final recommendation.',
                    'settings' => ['variant' => 'info'],
                    'is_required' => false,
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('data.name', 'Comparison')
            ->assertJsonPath('data.slug', 'comparison')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.blocks.1.block_type', 'callout');

        $this->withToken($token)->deleteJson("/api/v1/admin/templates/{$templateId}")
            ->assertNoContent();

        $this->assertSoftDeleted('templates', [
            'id' => $templateId,
        ]);
    }

    public function test_admin_template_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/templates', [
            'name' => '',
            'template_type' => 'landing-page',
            'status' => 'published',
            'blocks' => [
                [
                    'block_type' => 'unknown',
                    'sort_order' => 0,
                ],
            ],
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors',
                'meta' => ['request_id'],
            ]);
    }

    public function test_template_slugs_are_generated_and_uniquified_service_side(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        Template::query()->create([
            'created_by_user_id' => $admin->id,
            'updated_by_user_id' => $admin->id,
            'name' => 'Tutorial',
            'slug' => 'tutorial',
            'template_type' => 'tutorial',
            'description' => null,
            'status' => 'active',
            'default_excerpt_prompt' => null,
            'default_meta' => null,
        ]);

        $this->withToken($token)->postJson('/api/v1/admin/templates', [
            'name' => 'Tutorial',
            'template_type' => 'tutorial',
            'status' => 'active',
            'blocks' => [
                [
                    'block_type' => 'heading',
                    'sort_order' => 1,
                    'default_markdown' => '# {{title}}',
                    'is_required' => true,
                ],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.slug', 'tutorial-2');
    }
}
