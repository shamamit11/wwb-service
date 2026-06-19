<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_issues_a_sanctum_token(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ADMIN@example.com',
            'password' => 'password',
            'device_name' => 'test-suite',
        ])->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'abilities',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'is_admin',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.abilities.0', 'admin:access')
            ->assertJsonPath('data.user.id', $admin->id)
            ->assertJsonPath('data.user.is_admin', true);

        $this->assertCount(1, $admin->fresh()->tokens);
    }

    public function test_protected_admin_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/me')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');

        $this->postJson('/api/v1/admin/change-password', [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_non_admin_user_cannot_access_protected_admin_endpoint(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $token = $user->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/me')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN');
    }

    public function test_authenticated_admin_can_fetch_current_user_and_revoke_current_token(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $admin->id)
            ->assertJsonPath('data.is_admin', true);

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('data.revoked', true);

        $this->assertCount(0, $admin->fresh()->tokens);
    }

    public function test_authenticated_admin_can_change_their_password(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/admin/change-password', [
                'current_password' => 'password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertOk()
            ->assertJsonPath('data.password_changed', true);

        $this->assertTrue(Hash::check('new-password-123', (string) $admin->fresh()->password));
    }

    public function test_change_password_validates_current_password_and_confirmation(): void
    {
        $admin = User::factory()->create([
            'password' => 'password',
            'is_admin' => true,
        ]);

        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/admin/change-password', [
                'current_password' => 'wrong-password',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => [
                    'current_password',
                    'password',
                ],
            ]);
    }
}
