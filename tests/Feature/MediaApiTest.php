<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('r2');
        config()->set('filesystems.disks.r2.bucket', 'wwb-media');
    }

    public function test_admin_media_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/media')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_single_upload_persists_metadata_and_stores_the_file(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
        $file = UploadedFile::fake()->image('diagram.webp', 1600, 900);

        $response = $this->withToken($token)->postJson('/api/v1/admin/media', [
            'file' => $file,
            'alt_text' => 'Architecture diagram showing AI agent memory flows',
            'caption' => 'AI agent memory architecture',
            'source_type' => 'uploaded',
            'source_url' => null,
            'attribution_text' => null,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.source_type', 'uploaded')
            ->assertJsonPath('data.mime_type', $file->getMimeType())
            ->assertJsonPath('data.width', 1600)
            ->assertJsonPath('data.height', 900)
            ->assertJsonPath('data.alt_text', 'Architecture diagram showing AI agent memory flows')
            ->assertJsonPath('data.caption', 'AI agent memory architecture')
            ->assertJsonPath('data.status', 'ready');

        /** @var Media $media */
        $media = Media::query()->firstOrFail();

        Storage::disk('r2')->assertExists($media->object_key);
    }

    public function test_batch_upload_creates_multiple_media_rows(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/admin/media/batch', [
            'files' => [
                UploadedFile::fake()->image('first.png', 800, 600),
                UploadedFile::fake()->image('second.png', 640, 480),
            ],
            'source_type' => 'uploaded',
        ]);

        $response->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertSame(2, Media::query()->count());
    }

    public function test_metadata_is_editable_through_the_update_endpoint(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $media = Media::query()->create([
            'uploaded_by_user_id' => $admin->id,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'media/2026/06/example.webp',
            'original_filename' => 'example.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 12345,
            'checksum_sha256' => str_repeat('a', 64),
            'width' => 1200,
            'height' => 628,
            'alt_text' => null,
            'caption' => null,
            'source_type' => 'uploaded',
            'source_url' => null,
            'attribution_text' => null,
            'status' => 'ready',
            'metadata' => ['source' => 'seed'],
        ]);

        $this->withToken($token)->putJson("/api/v1/admin/media/{$media->id}", [
            'alt_text' => 'Updated alt text',
            'caption' => 'Updated caption',
            'source_type' => 'stock',
            'source_url' => 'https://example.com/original',
            'attribution_text' => 'Example Provider',
        ])->assertOk()
            ->assertJsonPath('data.alt_text', 'Updated alt text')
            ->assertJsonPath('data.caption', 'Updated caption')
            ->assertJsonPath('data.source_type', 'stock')
            ->assertJsonPath('data.source_url', 'https://example.com/original')
            ->assertJsonPath('data.attribution_text', 'Example Provider');

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'alt_text' => 'Updated alt text',
            'caption' => 'Updated caption',
            'source_type' => 'stock',
        ]);
    }

    public function test_media_validation_errors_use_consistent_json_shape(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/admin/media', [
            'source_type' => 'invalid-source',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['file', 'source_type'],
                'meta' => ['request_id'],
            ]);
    }
}
