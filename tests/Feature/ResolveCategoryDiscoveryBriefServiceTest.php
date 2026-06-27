<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Modules\Ai\Services\ResolveCategoryDiscoveryBriefService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResolveCategoryDiscoveryBriefServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolver_returns_ai_tools_brief_for_ai_tools_slug(): void
    {
        $author = User::factory()->create();
        $category = $this->createCategory($author, 'AI Tools', 'ai-tools');

        $brief = app(ResolveCategoryDiscoveryBriefService::class)->forCategory($category);

        $this->assertIsString($brief);
        $this->assertStringContainsString('practical, tool-specific, commercially relevant AI content', $brief);
        $this->assertStringContainsString('Favor named tools, tool categories, comparisons, reviews, alternatives', $brief);
    }

    public function test_resolver_returns_null_for_other_categories(): void
    {
        $author = User::factory()->create();
        $category = $this->createCategory($author, 'SEO', 'seo');

        $brief = app(ResolveCategoryDiscoveryBriefService::class)->forCategory($category);

        $this->assertNull($brief);
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
            'sort_order' => 1,
        ]);
    }
}
