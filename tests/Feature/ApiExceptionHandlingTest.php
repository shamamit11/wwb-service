<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_responses_use_the_data_envelope(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['status', 'service', 'version'],
            ])
            ->assertJsonPath('data.status', 'ok');
    }

    public function test_validation_errors_use_a_consistent_json_shape(): void
    {
        $this->postJson('/api/v1/test/echo', [])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['message'],
                'meta' => ['request_id'],
            ])
            ->assertJsonPath('error_code', 'VALIDATION_ERROR');
    }

    public function test_authentication_errors_use_a_consistent_json_shape(): void
    {
        $this->getJson('/api/v1/test/auth')
            ->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors',
                'meta' => ['request_id'],
            ])
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_not_found_errors_use_a_consistent_json_shape(): void
    {
        $this->getJson('/api/v1/test/users/999999')
            ->assertStatus(404)
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors',
                'meta' => ['request_id'],
            ])
            ->assertJsonPath('error_code', 'NOT_FOUND')
            ->assertJsonPath('message', 'Resource not found.');
    }

    public function test_internal_errors_do_not_leak_sensitive_details(): void
    {
        config()->set('app.debug', false);

        $this->getJson('/api/v1/test/error')
            ->assertStatus(500)
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors',
                'meta' => ['request_id'],
            ])
            ->assertJsonPath('error_code', 'INTERNAL_ERROR')
            ->assertJsonMissing(['message' => 'Sensitive internal details should not leak.']);
    }
}
