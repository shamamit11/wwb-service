<?php

namespace App\Modules\ContentBriefs\Services;

use App\Models\ContentBrief;
use App\Models\ContentTopic;
use App\Modules\ContentBriefs\Data\CreateContentBriefData;
use App\Modules\ContentBriefs\Data\GeneratedContentBriefData;
use App\Modules\ContentBriefs\Exceptions\ContentBriefGenerationNotAllowedException;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\Seo\Data\InternalLinkContextData;
use App\Modules\Seo\Services\SuggestInternalLinksService;
use App\Support\AuditActivityLogger;
use Illuminate\Support\Str;

class GenerateContentBriefFromTopicService
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
        private readonly ContentBriefSlugResolver $slugResolver,
        private readonly SuggestInternalLinksService $internalLinks,
        private readonly AuditActivityLogger $audit,
    ) {}

    public function handle(ContentTopic $topic): GeneratedContentBriefData
    {
        if (! $topic->isApproved()) {
            throw new ContentBriefGenerationNotAllowedException(
                topicStatus: $topic->status,
                message: "Content brief can only be generated from approved topics. Current status is [{$topic->status}].",
            );
        }

        $existing = $this->briefs->findByTopicId((int) $topic->id);

        if ($existing instanceof ContentBrief) {
            return new GeneratedContentBriefData($existing, false);
        }

        $title = $topic->title;
        $keyword = $topic->primary_keyword ?: $title;
        $headings = $this->headingsFor($topic, $keyword);
        $outline = $this->outlineFor($headings, $topic);
        $brief = $this->briefs->create(new CreateContentBriefData(
            contentTopicId: (int) $topic->id,
            title: $title,
            slug: $this->slugResolver->resolve($title),
            metaTitle: Str::limit($title, 60, ''),
            metaDescription: $this->metaDescriptionFor($topic, $keyword),
            primaryKeyword: $topic->primary_keyword,
            secondaryKeywords: $topic->secondary_keywords ?? [],
            searchIntent: $topic->search_intent,
            outline: $outline,
            headings: $headings,
            faqSuggestions: $this->faqSuggestionsFor($topic, $keyword),
            internalLinkSuggestions: $this->internalLinksFor($topic, $keyword),
            imageSuggestions: $this->imageSuggestionsFor($topic, $keyword),
            status: ContentBrief::STATUS_DRAFT,
        ));

        $this->audit->log(
            logName: 'content',
            description: 'content-brief.generated',
            event: 'generated',
            subject: $brief,
            attributes: [
                'content_topic_id' => $brief->content_topic_id,
                'status' => $brief->status,
                'title' => $brief->title,
            ],
        );

        return new GeneratedContentBriefData($brief, true);
    }

    private function metaDescriptionFor(ContentTopic $topic, string $keyword): string
    {
        $description = "A practical brief covering {$keyword}, search intent, editorial structure, FAQs, internal links, and image ideas for Wide Web Blog readers.";

        return Str::limit($description, 160, '');
    }

    /**
     * @return list<string>
     */
    private function headingsFor(ContentTopic $topic, string $keyword): array
    {
        return [
            "Why {$keyword} matters now",
            "How to approach {$topic->cluster} workflows",
            'Step-by-step implementation outline',
            'Common mistakes and tradeoffs',
            'Recommended next actions and measurement',
        ];
    }

    /**
     * @param  list<string>  $headings
     * @return list<array<string, mixed>>
     */
    private function outlineFor(array $headings, ContentTopic $topic): array
    {
        $purposes = [
            'Frame the opportunity and define the reader problem.',
            'Translate the topic into the cluster-specific editorial angle.',
            'Provide actionable steps, examples, or checklists.',
            'Highlight pitfalls, constraints, and decision points.',
            'Close with practical follow-up actions or metrics.',
        ];

        return array_map(
            static fn (string $heading, int $index): array => [
                'heading' => $heading,
                'purpose' => $purposes[$index] ?? 'Support the article angle with structured analysis.',
                'content_type' => $index === 2 ? 'checklist' : 'narrative',
            ],
            $headings,
            array_keys($headings),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function faqSuggestionsFor(ContentTopic $topic, string $keyword): array
    {
        return [
            [
                'question' => "What is the fastest way to start with {$keyword}?",
                'answer_focus' => 'Give a practical first-step workflow.',
            ],
            [
                'question' => "Which mistakes should teams avoid when working on {$keyword}?",
                'answer_focus' => 'Surface tradeoffs, failure modes, and review checks.',
            ],
            [
                'question' => "How should success be measured for {$keyword}?",
                'answer_focus' => 'Recommend metrics tied to the topic intent.',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function internalLinksFor(ContentTopic $topic, string $keyword): array
    {
        return $this->internalLinks->handleForContext(new InternalLinkContextData(
            title: $topic->title,
            excerpt: $this->metaDescriptionFor($topic, $keyword),
            tagNames: $topic->secondary_keywords ?? [],
            focusKeyword: $topic->primary_keyword,
        ), 3)->map(static fn ($link) => $link->toArray())->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function imageSuggestionsFor(ContentTopic $topic, string $keyword): array
    {
        return [
            [
                'placement' => 'hero',
                'idea' => "Editorial workflow illustration for {$keyword}",
                'alt_text' => "Diagram showing the core workflow for {$keyword}",
            ],
            [
                'placement' => 'mid_article',
                'idea' => "Checklist or process visual supporting the {$topic->cluster} angle",
                'alt_text' => "Checklist-style visual reinforcing the {$topic->title} article",
            ],
        ];
    }
}
