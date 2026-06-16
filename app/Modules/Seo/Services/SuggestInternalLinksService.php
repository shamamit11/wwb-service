<?php

namespace App\Modules\Seo\Services;

use App\Models\Post;
use App\Modules\Seo\Data\InternalLinkContextData;
use App\Modules\Seo\Data\InternalLinkSuggestionData;
use App\Modules\Seo\Data\RelatedContentCandidateData;
use Illuminate\Support\Collection;

class SuggestInternalLinksService
{
    public function __construct(
        private readonly FindRelatedContentService $relatedContent,
    ) {}

    /**
     * @return Collection<int, InternalLinkSuggestionData>
     */
    public function handleForPost(Post $post, int $limit = 5): Collection
    {
        return $this->mapSuggestions($this->relatedContent->handleForPost($post, $limit));
    }

    /**
     * @return Collection<int, InternalLinkSuggestionData>
     */
    public function handleForContext(InternalLinkContextData $context, int $limit = 5): Collection
    {
        return $this->mapSuggestions($this->relatedContent->handleForContext($context, $limit));
    }

    /**
     * @param  Collection<int, RelatedContentCandidateData>  $candidates
     * @return Collection<int, InternalLinkSuggestionData>
     */
    private function mapSuggestions(Collection $candidates): Collection
    {
        return $candidates->map(function (RelatedContentCandidateData $candidate): InternalLinkSuggestionData {
            $anchorText = $candidate->matchedTerms[0] ?? $candidate->title;

            return new InternalLinkSuggestionData(
                contentType: $candidate->contentType,
                id: $candidate->id,
                title: $candidate->title,
                slug: $candidate->slug,
                url: $candidate->url,
                score: $candidate->score,
                anchorText: $anchorText,
                reason: $this->reasonFor($candidate),
                matchedTerms: $candidate->matchedTerms,
                meta: $candidate->meta,
            );
        })->values();
    }

    private function reasonFor(RelatedContentCandidateData $candidate): string
    {
        if ($candidate->contentType === 'knowledge_base_entry') {
            return 'Knowledge base context overlaps with the draft or source post.';
        }

        return 'Published content shares overlapping category, tags, or editorial keywords.';
    }
}
