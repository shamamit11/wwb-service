<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Media\Data\UploadMediaData;
use App\Modules\Media\Services\Contracts\MediaDeleter;
use App\Modules\Media\Services\Contracts\MediaReader;
use App\Modules\Media\Services\Contracts\MediaUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_table_matches_the_service_contract_baseline(): void
    {
        $this->assertTrue(Schema::hasTable('media'));
        $this->assertTrue(Schema::hasColumns('media', [
            'id',
            'ulid',
            'uploaded_by_user_id',
            'generated_by_ai_job_id',
            'storage_provider',
            'bucket_name',
            'object_key',
            'original_filename',
            'mime_type',
            'extension',
            'file_size_bytes',
            'checksum_sha256',
            'width',
            'height',
            'alt_text',
            'caption',
            'source_type',
            'source_url',
            'attribution_text',
            'status',
            'metadata',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_upload_service_persists_media_metadata_and_stores_the_object(): void
    {
        Storage::fake('r2');
        config()->set('filesystems.disks.r2.bucket', 'wwb-media');

        $uploader = app(MediaUploader::class);
        $user = User::factory()->create(['is_admin' => true]);

        $media = $uploader->upload(new UploadMediaData(
            originalFilename: 'AI Agent Diagram.webp',
            mimeType: 'image/webp',
            contents: 'fake-binary-image',
            uploadedByUserId: $user->id,
            width: 1600,
            height: 900,
            altText: 'Architecture diagram showing AI agent memory flows',
            caption: 'AI agent memory architecture',
            sourceType: 'uploaded',
            metadata: ['source' => 'test-suite'],
        ));

        Storage::disk('r2')->assertExists($media->object_key);

        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'uploaded_by_user_id' => $user->id,
            'bucket_name' => 'wwb-media',
            'mime_type' => 'image/webp',
            'status' => 'ready',
            'source_type' => 'uploaded',
        ]);
    }

    public function test_read_and_delete_service_abstractions_exist_and_work(): void
    {
        Storage::fake('r2');
        config()->set('filesystems.disks.r2.bucket', 'wwb-media');

        $uploader = app(MediaUploader::class);
        $reader = app(MediaReader::class);
        $deleter = app(MediaDeleter::class);

        $media = $uploader->upload(new UploadMediaData(
            originalFilename: 'queue-architecture.png',
            mimeType: 'image/png',
            contents: 'binary-png',
        ));

        $found = $reader->findByUlid($media->ulid);

        $this->assertNotNull($found);
        $this->assertSame('binary-png', $reader->read($media));
        $this->assertStringContainsString($media->object_key, $reader->url($media));

        $deleted = $deleter->delete($media);

        Storage::disk('r2')->assertMissing($media->object_key);
        $this->assertSame('archived', $deleted->status);
        $this->assertSoftDeleted('media', [
            'id' => $media->id,
        ]);
    }
}
