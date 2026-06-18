<?php

namespace Tests\Feature;

use App\Models\AiPromptTemplate;
use App\Models\AiPromptTemplateVersion;
use App\Models\User;
use App\Modules\Ai\Services\RenderAiPromptTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiPromptTemplateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ai_prompt_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/ai-prompts')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_manage_prompt_templates_versions_and_activation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/ai-prompts', [
            'name' => 'Topic Discovery Default',
            'key' => 'topic_discovery_default',
            'type' => 'topic_discovery',
            'description' => 'Baseline discovery prompt.',
            'status' => 'active',
            'initial_version' => [
                'system_prompt' => 'You are a topic discovery assistant for {{cluster}}.',
                'user_prompt' => 'Suggest {{count}} article ideas using {{knowledge_summary}}.',
                'output_schema' => [
                    'type' => 'object',
                    'required' => ['topics'],
                ],
                'variables' => ['cluster', 'count', 'knowledge_summary'],
            ],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.key', 'topic_discovery_default')
            ->assertJsonPath('data.type', 'topic_discovery')
            ->assertJsonPath('data.active_version.version', 1)
            ->assertJsonPath('data.active_version.status', 'active')
            ->assertJsonPath('data.versions.0.variables.1', 'count');

        $templateId = (int) $createResponse->json('data.id');
        $firstVersionId = (int) $createResponse->json('data.active_version.id');

        $this->withToken($token)->getJson('/api/v1/admin/ai-prompts?type=topic_discovery&search=Baseline')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $templateId)
            ->assertJsonPath('data.0.active_version_id', $firstVersionId);

        $this->withToken($token)->getJson("/api/v1/admin/ai-prompts/{$templateId}")
            ->assertOk()
            ->assertJsonPath('data.active_version.version', 1)
            ->assertJsonCount(1, 'data.versions');

        $this->withToken($token)->patchJson("/api/v1/admin/ai-prompts/{$templateId}", [
            'name' => 'Topic Discovery Primary',
            'key' => 'topic_discovery_primary',
            'type' => 'topic_discovery',
            'description' => 'Updated discovery prompt.',
            'status' => 'active',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Topic Discovery Primary')
            ->assertJsonPath('data.key', 'topic_discovery_primary');

        $versionResponse = $this->withToken($token)->postJson("/api/v1/admin/ai-prompts/{$templateId}/versions", [
            'system_prompt' => 'You are a refined topic discovery assistant for {{cluster}}.',
            'user_prompt' => 'Return {{count}} ideas that avoid overlap with {{existing_topics}}.',
            'output_schema' => [
                'type' => 'object',
                'required' => ['topics'],
            ],
            'variables' => ['cluster', 'count', 'existing_topics'],
            'status' => 'draft',
        ]);

        $versionResponse->assertCreated()
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.variables.2', 'existing_topics');

        $versionId = (int) $versionResponse->json('data.id');

        $this->withToken($token)->postJson("/api/v1/admin/ai-prompts/{$templateId}/activate-version/{$versionId}")
            ->assertOk()
            ->assertJsonPath('data.active_version_id', $versionId)
            ->assertJsonPath('data.active_version.version', 2)
            ->assertJsonPath('data.active_version.status', 'active')
            ->assertJsonCount(2, 'data.versions');

        $this->assertDatabaseHas('ai_prompt_templates', [
            'id' => $templateId,
            'active_version_id' => $versionId,
            'key' => 'topic_discovery_primary',
        ]);

        $this->assertDatabaseHas('ai_prompt_template_versions', [
            'id' => $firstVersionId,
            'status' => 'archived',
        ]);

        $this->assertDatabaseHas('ai_prompt_template_versions', [
            'id' => $versionId,
            'version' => 2,
            'status' => 'active',
        ]);
    }

    public function test_prompt_rendering_replaces_known_variables_and_reports_missing_ones_safely(): void
    {
        $template = AiPromptTemplate::query()->create([
            'name' => 'Blog Writer',
            'key' => 'blog_writer_default',
            'type' => AiPromptTemplate::TYPE_BLOG_WRITER,
            'description' => null,
            'status' => AiPromptTemplate::STATUS_ACTIVE,
        ]);

        $version = AiPromptTemplateVersion::query()->create([
            'prompt_template_id' => $template->id,
            'version' => 1,
            'system_prompt' => 'Use the {{tone}} tone.',
            'user_prompt' => 'Write about {{topic}} with sections {{sections}} and {{missing_var}}.',
            'output_schema' => ['type' => 'object'],
            'variables' => ['tone', 'topic', 'sections', 'missing_var'],
            'status' => AiPromptTemplateVersion::STATUS_ACTIVE,
        ]);

        $template->update(['active_version_id' => $version->id]);
        $template->load('activeVersion');

        $rendered = app(RenderAiPromptTemplateService::class)->render($template, [
            'tone' => 'practical',
            'topic' => 'AI memory',
            'sections' => ['intro', 'patterns'],
        ]);

        $this->assertSame('Use the practical tone.', $rendered->systemPrompt);
        $this->assertStringContainsString('AI memory', $rendered->userPrompt);
        $this->assertStringContainsString('["intro","patterns"]', $rendered->userPrompt);
        $this->assertStringContainsString('{{missing_var}}', $rendered->userPrompt);
        $this->assertSame(['missing_var'], $rendered->missingVariables);
    }

    public function test_prompt_template_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/ai-prompts', [
            'name' => '',
            'key' => 'Bad Key',
            'type' => 'unknown',
            'status' => 'published',
            'initial_version' => [
                'system_prompt' => '',
                'user_prompt' => '',
                'variables' => ['topic', 'topic'],
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
}
