<?php

namespace Database\Seeders;

use App\Models\ContentTopic;
use Illuminate\Database\Seeder;

class ContentTopicSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->records() as $attributes) {
            ContentTopic::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(): array
    {
        return [
            [
                'title' => 'AI Content Audit Checklist for Editorial Teams',
                'slug' => 'ai-content-audit-checklist-for-editorial-teams',
                'cluster' => ContentTopic::CLUSTER_AI_FOR_BLOGGING,
                'primary_keyword' => 'ai content audit checklist',
                'secondary_keywords' => ['editorial qa', 'content review workflow'],
                'search_intent' => 'informational',
                'priority_score' => '88.00',
                'difficulty_note' => 'Strong practical angle with clear editorial workflow relevance.',
                'source' => ContentTopic::SOURCE_AI_SUGGESTED,
                'status' => ContentTopic::STATUS_APPROVED,
                'notes' => 'Approved for brief and draft generation demos.',
                'approved_at' => now()->subDay(),
                'rejected_at' => null,
                'used_at' => null,
            ],
            [
                'title' => 'Best AI Research Workflows for Blog Planning',
                'slug' => 'best-ai-research-workflows-for-blog-planning',
                'cluster' => ContentTopic::CLUSTER_AI_TOOLS,
                'primary_keyword' => 'ai research workflow',
                'secondary_keywords' => ['blog planning', 'topic research'],
                'search_intent' => 'commercial',
                'priority_score' => '74.00',
                'difficulty_note' => 'Needs clearer product positioning before approval.',
                'source' => ContentTopic::SOURCE_AI_SUGGESTED,
                'status' => ContentTopic::STATUS_SUGGESTED,
                'notes' => 'Queued for editorial review.',
                'approved_at' => null,
                'rejected_at' => null,
                'used_at' => null,
            ],
            [
                'title' => 'Laravel Queue Guardrails for AI Jobs',
                'slug' => 'laravel-queue-guardrails-for-ai-jobs',
                'cluster' => ContentTopic::CLUSTER_DEVELOPER_AI,
                'primary_keyword' => 'laravel ai queue',
                'secondary_keywords' => ['idempotent jobs', 'queue retries'],
                'search_intent' => 'informational',
                'priority_score' => '81.00',
                'difficulty_note' => null,
                'source' => ContentTopic::SOURCE_MANUAL,
                'status' => ContentTopic::STATUS_USED,
                'notes' => 'Already used for an approved brief and draft.',
                'approved_at' => now()->subDays(3),
                'rejected_at' => null,
                'used_at' => now()->subDays(2),
            ],
        ];
    }
}
