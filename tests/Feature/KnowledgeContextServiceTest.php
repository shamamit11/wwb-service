<?php

namespace Tests\Feature;

use App\Models\KnowledgeBaseEntry;
use App\Models\User;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use App\Modules\KnowledgeBase\Services\KnowledgeContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeContextServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_prioritizes_relevant_active_entries_for_agent_context(): void
    {
        $author = User::factory()->create();

        $unrelated = $this->createEntry($author, [
            'title' => 'Garden Planning Notes',
            'slug' => 'garden-planning-notes',
            'summary' => 'Seasonal checklist for container gardening.',
            'content_markdown' => 'Tomatoes, herbs, and balcony lighting.',
        ]);

        $relevant = $this->createEntry($author, [
            'title' => 'AI Editorial Review Workflow',
            'slug' => 'ai-editorial-review-workflow',
            'entry_type' => KnowledgeBaseEntry::TYPE_ARCHITECTURE,
            'summary' => 'Use editorial review gates to verify AI-assisted drafts before publication.',
            'content_markdown' => 'Review workflow, approval steps, and QA ownership.',
        ]);

        $this->createEntry($author, [
            'title' => 'Archived Editorial Notes',
            'slug' => 'archived-editorial-notes',
            'status' => KnowledgeBaseEntry::STATUS_ARCHIVED,
            'summary' => 'Should not appear in agent context.',
        ]);

        $results = app(KnowledgeContextService::class)->search(new KnowledgeContextQueryData(
            subject: 'AI editorial review',
            keywords: ['workflow', 'publication'],
        ));

        $this->assertSame($relevant->id, $results->first()?->id);
        $this->assertTrue($results->contains(fn (KnowledgeBaseEntry $entry): bool => $entry->id === $unrelated->id));
        $this->assertFalse($results->contains(fn (KnowledgeBaseEntry $entry): bool => $entry->slug === 'archived-editorial-notes'));
    }

    public function test_service_supports_optional_metadata_filters(): void
    {
        $author = User::factory()->create();

        $matching = $this->createEntry($author, [
            'title' => 'Editorial Systems',
            'slug' => 'editorial-systems',
            'summary' => 'Ops notes for editorial systems.',
            'metadata' => [
                'clusters' => ['ai_for_blogging', 'seo'],
                'audience' => 'editorial',
            ],
        ]);

        $this->createEntry($author, [
            'title' => 'Developer Systems',
            'slug' => 'developer-systems',
            'summary' => 'Ops notes for developer systems.',
            'metadata' => [
                'clusters' => ['developer_ai'],
                'audience' => 'engineering',
            ],
        ]);

        $lines = app(KnowledgeContextService::class)->forPrompt(new KnowledgeContextQueryData(
            subject: 'systems',
            metadataFilters: [
                'clusters' => ['ai_for_blogging'],
                'audience' => 'editorial',
            ],
        ));

        $this->assertCount(1, $lines);
        $this->assertStringContainsString($matching->title, $lines[0]);
    }

    public function test_service_formats_context_with_agent_safe_size_limits(): void
    {
        $author = User::factory()->create();

        $this->createEntry($author, [
            'title' => 'Editorial QA',
            'slug' => 'editorial-qa',
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'summary' => str_repeat('Use explicit review gates and source checks. ', 8),
            'source_url' => 'https://example.com/editorial-qa',
        ]);

        $this->createEntry($author, [
            'title' => 'Prompt Guardrails',
            'slug' => 'prompt-guardrails',
            'summary' => str_repeat('Keep prompts versioned and auditable. ', 8),
        ]);

        $lines = app(KnowledgeContextService::class)->forPrompt(new KnowledgeContextQueryData(
            subject: 'editorial qa',
            keywords: ['prompts'],
            maxEntries: 2,
            maxEntryCharacters: 120,
            maxTotalCharacters: 140,
        ));

        $this->assertNotEmpty($lines);
        $this->assertLessThanOrEqual(140, array_sum(array_map(static fn (string $line): int => mb_strlen($line), $lines)));
        $this->assertLessThanOrEqual(120, max(array_map(static fn (string $line): int => mb_strlen($line), $lines)));
        $this->assertStringContainsString('[reference] Editorial QA:', $lines[0]);
        $this->assertStringContainsString('Source:', $lines[0]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createEntry(User $author, array $overrides = []): KnowledgeBaseEntry
    {
        return KnowledgeBaseEntry::query()->create(array_merge([
            'created_by_user_id' => $author->id,
            'updated_by_user_id' => $author->id,
            'title' => 'Knowledge Entry',
            'slug' => 'knowledge-entry-'.fake()->unique()->slug(),
            'entry_type' => KnowledgeBaseEntry::TYPE_REFERENCE,
            'status' => KnowledgeBaseEntry::STATUS_ACTIVE,
            'summary' => 'Knowledge summary.',
            'content_markdown' => 'Knowledge markdown.',
            'source_url' => null,
            'featured_media_id' => null,
            'metadata' => null,
        ], $overrides));
    }
}
