<?php

namespace Database\Seeders;

use App\Models\ContentBrief;
use App\Models\ContentTopic;
use Illuminate\Database\Seeder;

class ContentBriefSeeder extends Seeder
{
    public function run(): void
    {
        $topics = ContentTopic::query()
            ->whereIn('slug', [
                'ai-content-audit-checklist-for-editorial-teams',
                'laravel-queue-guardrails-for-ai-jobs',
            ])
            ->get()
            ->keyBy('slug');

        foreach ($this->records($topics) as $attributes) {
            ContentBrief::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<string, ContentTopic>  $topics
     * @return list<array<string, mixed>>
     */
    private function records($topics): array
    {
        $approvedTopic = $topics->get('ai-content-audit-checklist-for-editorial-teams');
        $usedTopic = $topics->get('laravel-queue-guardrails-for-ai-jobs');

        return array_values(array_filter([
            $approvedTopic instanceof ContentTopic ? [
                'content_topic_id' => $approvedTopic->id,
                'title' => 'AI Content Audit Checklist for Editorial Teams',
                'slug' => 'ai-content-audit-checklist-for-editorial-teams',
                'meta_title' => 'AI Content Audit Checklist for Editorial Teams',
                'meta_description' => 'A structured editorial brief for auditing AI-assisted drafts before publication.',
                'primary_keyword' => 'ai content audit checklist',
                'secondary_keywords' => ['editorial qa', 'content review workflow'],
                'search_intent' => 'informational',
                'outline' => [
                    ['heading' => 'Why AI draft audits matter', 'purpose' => 'Frame the editorial risk and need for process'],
                    ['heading' => 'Build a repeatable review checklist', 'purpose' => 'Show what editors should verify'],
                ],
                'headings' => ['Why AI draft audits matter', 'Build a repeatable review checklist'],
                'faq_suggestions' => [
                    ['question' => 'What should an AI content audit cover?', 'answer_focus' => 'Accuracy, links, voice, and SEO basics'],
                ],
                'internal_link_suggestions' => [
                    ['anchor' => 'queue retry patterns', 'target_slug' => 'laravel-queue-timeout-patterns'],
                ],
                'image_suggestions' => [
                    ['idea' => 'Editorial checklist diagram', 'alt_text' => 'Checklist-based AI content review workflow'],
                ],
                'status' => ContentBrief::STATUS_APPROVED,
                'approved_at' => now()->subHours(18),
            ] : null,
            $usedTopic instanceof ContentTopic ? [
                'content_topic_id' => $usedTopic->id,
                'title' => 'Laravel Queue Guardrails for AI Jobs',
                'slug' => 'laravel-queue-guardrails-for-ai-jobs',
                'meta_title' => 'Laravel Queue Guardrails for AI Jobs',
                'meta_description' => 'A used brief covering retries, idempotency, and queue failure handling for AI workflows.',
                'primary_keyword' => 'laravel ai queue',
                'secondary_keywords' => ['idempotent jobs', 'queue retries'],
                'search_intent' => 'informational',
                'outline' => [
                    ['heading' => 'Why AI jobs need queue guardrails', 'purpose' => 'Explain failure modes'],
                    ['heading' => 'Idempotency and retry safety', 'purpose' => 'Show implementation rules'],
                ],
                'headings' => ['Why AI jobs need queue guardrails', 'Idempotency and retry safety'],
                'faq_suggestions' => [],
                'internal_link_suggestions' => [],
                'image_suggestions' => [],
                'status' => ContentBrief::STATUS_USED,
                'approved_at' => now()->subDays(3),
            ] : null,
        ]));
    }
}
