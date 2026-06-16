<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Templates\Data\CreateTemplateBlockData;
use App\Modules\Templates\Data\CreateTemplateData;
use App\Modules\Templates\Data\UpdateTemplateData;
use App\Modules\Templates\Repositories\TemplateRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TemplateRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_templates_and_template_blocks_tables_match_the_design_baseline(): void
    {
        $this->assertTrue(Schema::hasTable('templates'));
        $this->assertTrue(Schema::hasColumns('templates', [
            'id',
            'ulid',
            'created_by_user_id',
            'updated_by_user_id',
            'name',
            'slug',
            'template_type',
            'description',
            'status',
            'default_excerpt_prompt',
            'default_meta',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));

        $this->assertTrue(Schema::hasTable('template_blocks'));
        $this->assertTrue(Schema::hasColumns('template_blocks', [
            'id',
            'template_id',
            'block_key',
            'block_type',
            'sort_order',
            'label',
            'default_markdown',
            'settings',
            'is_required',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_repository_supports_template_persistence_and_ordered_block_retrieval(): void
    {
        $repository = app(TemplateRepository::class);
        $creator = User::factory()->create(['is_admin' => true]);
        $editor = User::factory()->create(['is_admin' => true]);

        $template = $repository->create(new CreateTemplateData(
            createdByUserId: $creator->id,
            updatedByUserId: null,
            name: 'Tutorial',
            slug: 'tutorial',
            templateType: 'tutorial',
            description: 'Structured step-by-step tutorial layout',
            status: 'active',
            defaultExcerptPrompt: 'Summarize the steps and outcomes.',
            defaultMeta: [
                'recommended_sections' => ['intro', 'prerequisites', 'steps', 'faq'],
            ],
            blocks: [
                new CreateTemplateBlockData(
                    blockType: 'paragraph',
                    sortOrder: 2,
                    label: 'Introduction',
                    defaultMarkdown: 'Intro content',
                    settings: ['markdown' => true],
                    isRequired: true,
                ),
                new CreateTemplateBlockData(
                    blockType: 'heading',
                    sortOrder: 1,
                    label: 'Title',
                    defaultMarkdown: '# {{title}}',
                    settings: ['level' => 1],
                    isRequired: true,
                ),
            ],
        ));

        $orderedBlocks = $repository->getOrderedBlocks($template);

        $this->assertCount(2, $orderedBlocks);
        $this->assertSame(['heading', 'paragraph'], $orderedBlocks->pluck('block_type')->all());

        $updated = $repository->update($template, new UpdateTemplateData(
            updatedByUserId: $editor->id,
            name: 'Comparison',
            slug: 'comparison',
            templateType: 'comparison',
            description: 'Tradeoff-oriented comparison layout',
            status: 'draft',
            defaultExcerptPrompt: 'Summarize the compared options.',
            defaultMeta: [
                'recommended_sections' => ['problem', 'option-a', 'option-b', 'recommendation'],
            ],
            blocks: [
                new CreateTemplateBlockData(
                    blockType: 'heading',
                    sortOrder: 1,
                    label: 'Title',
                    defaultMarkdown: '# {{title}}',
                    settings: ['level' => 1],
                    isRequired: true,
                ),
                new CreateTemplateBlockData(
                    blockType: 'callout',
                    sortOrder: 2,
                    label: 'Decision summary',
                    defaultMarkdown: 'Use this block for the final recommendation.',
                    settings: ['variant' => 'info'],
                    isRequired: false,
                ),
            ],
        ));

        $this->assertSame($updated->id, $repository->findById($updated->id)?->id);
        $this->assertSame($updated->id, $repository->findBySlug('comparison')?->id);

        $updatedBlocks = $repository->getOrderedBlocks($updated);

        $this->assertSame(['heading', 'callout'], $updatedBlocks->pluck('block_type')->all());
        $this->assertDatabaseHas('templates', [
            'id' => $updated->id,
            'created_by_user_id' => $creator->id,
            'updated_by_user_id' => $editor->id,
            'name' => 'Comparison',
            'slug' => 'comparison',
            'template_type' => 'comparison',
            'status' => 'draft',
        ]);
        $this->assertSame(26, strlen($updated->ulid));
        $this->assertCount(2, $updatedBlocks);
    }

    public function test_repository_delete_soft_deletes_template_and_removes_blocks(): void
    {
        $repository = app(TemplateRepository::class);
        $creator = User::factory()->create(['is_admin' => true]);

        $template = $repository->create(new CreateTemplateData(
            createdByUserId: $creator->id,
            updatedByUserId: null,
            name: 'News',
            slug: 'news',
            templateType: 'news',
            description: null,
            status: 'active',
            defaultExcerptPrompt: null,
            defaultMeta: null,
            blocks: [
                new CreateTemplateBlockData(
                    blockType: 'heading',
                    sortOrder: 1,
                    isRequired: true,
                ),
            ],
        ));

        $blockIds = $repository->getOrderedBlocks($template)->modelKeys();

        $repository->delete($template);

        $this->assertSoftDeleted('templates', [
            'id' => $template->id,
        ]);
        $this->assertDatabaseMissing('template_blocks', [
            'id' => $blockIds[0],
        ]);
    }
}
