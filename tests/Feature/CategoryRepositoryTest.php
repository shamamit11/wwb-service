<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Modules\Categories\Data\CreateCategoryData;
use App\Modules\Categories\Data\UpdateCategoryData;
use App\Modules\Categories\Repositories\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_table_matches_the_documented_design_baseline(): void
    {
        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasColumns('categories', [
            'id',
            'ulid',
            'parent_id',
            'created_by_user_id',
            'updated_by_user_id',
            'name',
            'slug',
            'description',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_repository_supports_core_category_reads_and_writes(): void
    {
        $repository = app(CategoryRepository::class);
        $creator = User::factory()->create(['is_admin' => true]);
        $editor = User::factory()->create(['is_admin' => true]);

        $parent = $repository->create(new CreateCategoryData(
            parentId: null,
            createdByUserId: $creator->id,
            updatedByUserId: null,
            name: 'Parent Category',
            slug: 'parent-category',
            description: 'Parent description',
            isActive: true,
            sortOrder: 5,
        ));

        $child = $repository->create(new CreateCategoryData(
            parentId: $parent->id,
            createdByUserId: $creator->id,
            updatedByUserId: null,
            name: 'Child Category',
            slug: 'child-category',
            description: 'Child description',
            isActive: false,
            sortOrder: 20,
        ));

        $updated = $repository->update($child, new UpdateCategoryData(
            parentId: $parent->id,
            updatedByUserId: $editor->id,
            name: 'AI Agents',
            slug: 'ai-agents',
            description: 'Technical content about agents.',
            isActive: true,
            sortOrder: 10,
        ));

        $this->assertSame($updated->id, $repository->findById($updated->id)?->id);
        $this->assertSame($updated->id, $repository->findBySlug('ai-agents')?->id);

        $activeCategories = $repository->getActiveOrdered();

        $this->assertCount(2, $activeCategories);
        $this->assertSame([$parent->id, $updated->id], $activeCategories->modelKeys());

        $this->assertDatabaseHas('categories', [
            'id' => $updated->id,
            'parent_id' => $parent->id,
            'created_by_user_id' => $creator->id,
            'updated_by_user_id' => $editor->id,
            'name' => 'AI Agents',
            'slug' => 'ai-agents',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $this->assertSame(26, strlen($updated->ulid));
        $this->assertSame($parent->id, $updated->parent()->first()?->getKey());
    }

    public function test_category_model_uses_soft_deletes(): void
    {
        $creator = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create([
            'created_by_user_id' => $creator->id,
            'name' => 'Draft Category',
            'slug' => 'draft-category',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category->delete();

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);
    }
}
