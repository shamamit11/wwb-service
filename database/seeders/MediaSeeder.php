<?php

namespace Database\Seeders;

use App\Models\Media;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();

        if ($admin === null) {
            $this->warn('Skipping media seeding because no user record exists.');

            return;
        }

        foreach ($this->records($admin->id) as $attributes) {
            Media::query()->updateOrCreate(
                [
                    'storage_provider' => $attributes['storage_provider'],
                    'bucket_name' => $attributes['bucket_name'],
                    'object_key' => $attributes['object_key'],
                ],
                $attributes,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(int $adminUserId): array
    {
        return [
            [
                'uploaded_by_user_id' => $adminUserId,
                'generated_by_ai_job_id' => null,
                'storage_provider' => 'r2',
                'bucket_name' => 'wwb-media',
                'object_key' => 'seed/media/ai-agent-memory-cover.webp',
                'original_filename' => 'ai-agent-memory-cover.webp',
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'file_size_bytes' => 182734,
                'checksum_sha256' => str_repeat('a', 64),
                'width' => 1600,
                'height' => 900,
                'alt_text' => 'Abstract illustration for AI agent memory systems',
                'caption' => 'Seeded editorial cover image.',
                'source_type' => 'uploaded',
                'source_url' => null,
                'attribution_text' => null,
                'status' => 'ready',
                'metadata' => [
                    'collection' => 'seed',
                    'role' => 'featured',
                ],
            ],
            [
                'uploaded_by_user_id' => $adminUserId,
                'generated_by_ai_job_id' => null,
                'storage_provider' => 'r2',
                'bucket_name' => 'wwb-media',
                'object_key' => 'seed/media/laravel-queues-reference.webp',
                'original_filename' => 'laravel-queues-reference.webp',
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'file_size_bytes' => 145220,
                'checksum_sha256' => str_repeat('b', 64),
                'width' => 1200,
                'height' => 630,
                'alt_text' => 'Queue processing diagram for Laravel workers',
                'caption' => 'Seeded knowledge base reference image.',
                'source_type' => 'uploaded',
                'source_url' => null,
                'attribution_text' => null,
                'status' => 'ready',
                'metadata' => [
                    'collection' => 'seed',
                    'role' => 'reference',
                ],
            ],
        ];
    }
}
