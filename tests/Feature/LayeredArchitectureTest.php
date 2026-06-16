<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayeredArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_user_flow_uses_the_layered_convention_successfully(): void
    {
        $response = $this->postJson('/api/v1/test/users', [
            'name' => 'Layered Example',
            'email' => 'Layered.User@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.name', 'Layered Example')
            ->assertJsonPath('data.email', 'layered.user@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'layered.user@example.com',
            'name' => 'Layered Example',
        ]);
    }

    public function test_sample_user_flow_uses_request_validation_before_service_execution(): void
    {
        $this->postJson('/api/v1/test/users', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR');

        $this->assertSame(0, User::query()->count());
    }
}
