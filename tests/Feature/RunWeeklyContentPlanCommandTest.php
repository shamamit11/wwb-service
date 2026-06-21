<?php

namespace Tests\Feature;

use App\Models\AiJob;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RunWeeklyContentPlanCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_queues_sunday_pillar_plan_with_two_topic_target(): void
    {
        $author = User::factory()->create();
        $category = $this->createCategory($author, 'Developer AI', 'developer-ai');

        $this->artisan('ai:run-weekly-content-plan', ['--day' => 'sunday'])
            ->assertSuccessful();

        $job = AiJob::query()->latest('id')->first();

        $this->assertNotNull($job);
        $this->assertSame('topic_discovery', $job->type);
        $this->assertSame($category->id, $job->input_payload['category_id']);
        $this->assertSame(2, $job->input_payload['count']);
        $this->assertSame('Long-form Pillar Article', $job->input_payload['metadata']['content_plan_theme']);
        $this->assertSame('pillar', $job->input_payload['metadata']['content_plan_format']);
        $this->assertSame('weekly_content_plan', $job->input_payload['metadata']['trigger']);
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
