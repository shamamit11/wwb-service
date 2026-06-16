<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_changes_are_recorded_in_activity_log(): void
    {
        $user = User::factory()->create([
            'name' => 'Initial Author',
            'email' => 'author@example.com',
        ]);

        $user->update([
            'name' => 'Updated Author',
        ]);

        $activities = Activity::query()->orderBy('id')->get();

        $this->assertCount(2, $activities);

        $this->assertSame('auth', $activities[0]->log_name);
        $this->assertSame('user.created', $activities[0]->description);
        $this->assertSame('created', $activities[0]->event);
        $this->assertSame('Initial Author', $activities[0]->attribute_changes['attributes']['name']);

        $this->assertSame('auth', $activities[1]->log_name);
        $this->assertSame('user.updated', $activities[1]->description);
        $this->assertSame('updated', $activities[1]->event);
        $this->assertSame('Updated Author', $activities[1]->attribute_changes['attributes']['name']);
        $this->assertSame('Initial Author', $activities[1]->attribute_changes['old']['name']);
    }
}
