<?php

namespace Tests\Feature;

use App\Models\KnowledgeBaseEntry;
use App\Models\Media;
use App\Models\User;
use App\Modules\KnowledgeBase\Data\CreateKnowledgeBaseEntryData;
use App\Modules\KnowledgeBase\Data\UpdateKnowledgeBaseEntryData;
use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KnowledgeBaseEntryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_knowledge_base_entries_table_matches_the_design_baseline(): void
    {
        $this->assertTrue(Schema::hasTable('knowledge_base_entries'));
        $this->assertTrue(Schema::hasColumns('knowledge_base_entries', [
            'id',
            'ulid',
            'created_by_user_id',
            'updated_by_user_id',
            'title',
            'slug',
            'entry_type',
            'status',
            'summary',
            'content_markdown',
            'source_url',
            'featured_media_id',
            'metadata',
            'created_at',
            'updated_at',
            'deleted_at',
        ]));
    }

    public function test_repository_persists_entry_type_and_status(): void
    {
        $repository = app(KnowledgeBaseEntryRepository::class);
        $creator = User::factory()->create(['is_admin' => true]);
        $editor = User::factory()->create(['is_admin' => true]);
        $featuredMedia = $this->createMedia($creator, 'kb-entry.webp');

        $entry = $repository->create(new CreateKnowledgeBaseEntryData(
            createdByUserId: $creator->id,
            updatedByUserId: null,
            title: 'Laravel Queue Retry Patterns',
            slug: 'laravel-queue-retry-patterns',
            entryType: KnowledgeBaseEntry::TYPE_ARCHITECTURE,
            status: KnowledgeBaseEntry::STATUS_ACTIVE,
            summary: 'Notes on retries, timeouts, and dead-letter handling.',
            contentMarkdown: 'When using queues for AI jobs, idempotency matters.',
            sourceUrl: 'https://laravel.com/docs/queues',
            featuredMediaId: $featuredMedia->id,
            metadata: [
                'tags' => ['laravel', 'queues', 'ai'],
            ],
        ));

        $this->assertSame($entry->id, $repository->findById($entry->id)?->id);
        $this->assertSame($entry->id, $repository->findBySlug('laravel-queue-retry-patterns')?->id);
        $this->assertTrue($repository->existsBySlug('laravel-queue-retry-patterns'));
        $this->assertSame([$entry->id], $repository->getAllOrdered()->modelKeys());
        $this->assertSame(KnowledgeBaseEntry::TYPE_ARCHITECTURE, $entry->entry_type);
        $this->assertSame(KnowledgeBaseEntry::STATUS_ACTIVE, $entry->status);
        $this->assertSame($featuredMedia->id, $entry->featuredMedia?->id);
        $this->assertSame(26, strlen($entry->ulid));

        $updated = $repository->update($entry, new UpdateKnowledgeBaseEntryData(
            updatedByUserId: $editor->id,
            title: 'Laravel Queue Timeout Patterns',
            slug: 'laravel-queue-timeout-patterns',
            entryType: KnowledgeBaseEntry::TYPE_REFERENCE,
            status: KnowledgeBaseEntry::STATUS_ARCHIVED,
            summary: 'Timeout and worker-control notes.',
            contentMarkdown: 'Archive older retry notes after consolidating patterns.',
            sourceUrl: null,
            featuredMediaId: null,
            metadata: [
                'archived_reason' => 'merged into a broader guide',
            ],
        ));

        $this->assertSame($updated->id, $repository->findBySlug('laravel-queue-timeout-patterns')?->id);
        $this->assertSame($editor->id, $updated->updatedBy?->id);
        $this->assertSame(KnowledgeBaseEntry::TYPE_REFERENCE, $updated->entry_type);
        $this->assertSame(KnowledgeBaseEntry::STATUS_ARCHIVED, $updated->status);
        $this->assertNull($updated->featuredMedia);
        $this->assertDatabaseHas('knowledge_base_entries', [
            'id' => $updated->id,
            'created_by_user_id' => $creator->id,
            'updated_by_user_id' => $editor->id,
            'slug' => 'laravel-queue-timeout-patterns',
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => KnowledgeBaseEntry::STATUS_ARCHIVED,
        ]);
    }

    public function test_repository_delete_soft_deletes_knowledge_base_entry(): void
    {
        $repository = app(KnowledgeBaseEntryRepository::class);
        $creator = User::factory()->create(['is_admin' => true]);

        $entry = $repository->create(new CreateKnowledgeBaseEntryData(
            createdByUserId: $creator->id,
            updatedByUserId: null,
            title: 'Deletable Knowledge Item',
            slug: 'deletable-knowledge-item',
            entryType: KnowledgeBaseEntry::TYPE_NOTE,
            status: KnowledgeBaseEntry::STATUS_DRAFT,
            summary: null,
            contentMarkdown: 'Draft note content.',
            sourceUrl: null,
            featuredMediaId: null,
            metadata: null,
        ));

        $repository->delete($entry);

        $this->assertSoftDeleted('knowledge_base_entries', [
            'id' => $entry->id,
        ]);
    }

    private function createMedia(User $author, string $filename): Media
    {
        return Media::query()->create([
            'uploaded_by_user_id' => $author->id,
            'generated_by_ai_job_id' => null,
            'storage_provider' => 'r2',
            'bucket_name' => 'wwb-media',
            'object_key' => 'knowledge-base/'.$filename,
            'original_filename' => $filename,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size_bytes' => 1024,
            'checksum_sha256' => str_repeat('b', 64),
            'width' => 1200,
            'height' => 630,
            'alt_text' => null,
            'caption' => null,
            'source_type' => 'uploaded',
            'source_url' => null,
            'attribution_text' => null,
            'status' => 'ready',
            'metadata' => null,
        ]);
    }
}
