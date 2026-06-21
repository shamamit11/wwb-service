<?php

namespace App\Modules\Seo\Services;

use App\Models\KnowledgeBaseEntry;
use App\Models\Post;
use App\Modules\Posts\Repositories\PostRepository;
use App\Modules\Seo\Data\InternalLinkContextData;
use App\Modules\Seo\Data\RelatedContentCandidateData;
use Illuminate\Support\Collection;

class FindRelatedContentService
{
    public function __construct(
        private readonly PostRepository $posts,
        private readonly CanonicalUrlService $canonicalUrls,
    ) {}

    /**
     * @return Collection<int, RelatedContentCandidateData>
     */
    public function handleForPost(Post $post, int $limit = 5): Collection
    {
        $post->loadMissing(['category', 'tags', 'seo']);

        return $this->handleForContext($this->contextFromPost($post), $limit);
    }

    /**
     * @return Collection<int, RelatedContentCandidateData>
     */
    public function handleForContext(InternalLinkContextData $context, int $limit = 5): Collection
    {
        $candidates = array_merge(
            $this->scorePublishedPosts($context),
            $this->scoreKnowledgeBaseEntries($context),
        );

        usort($candidates, function (RelatedContentCandidateData $left, RelatedContentCandidateData $right): int {
            if ($left->score !== $right->score) {
                return $right->score <=> $left->score;
            }

            if ($left->contentType !== $right->contentType) {
                return $this->contentTypePriority($left->contentType) <=> $this->contentTypePriority($right->contentType);
            }

            return $left->title <=> $right->title;
        });

        return collect($candidates)
            ->take($limit)
            ->values();
    }

    private function contextFromPost(Post $post): InternalLinkContextData
    {
        return new InternalLinkContextData(
            title: $post->title,
            excerpt: $post->short_description,
            categoryId: $post->category_id,
            tagNames: $post->tags->pluck('name')->all(),
            focusKeyword: $post->seo?->focus_keyword,
            excludePostId: $post->id,
        );
    }

    /**
     * @return list<RelatedContentCandidateData>
     */
    private function scorePublishedPosts(InternalLinkContextData $context): array
    {
        $contextTerms = $this->terms(
            $context->title,
            $context->excerpt,
            $context->focusKeyword,
            ...$context->tagNames,
        );
        $contextTags = $this->normalizeLabels($context->tagNames);

        return $this->posts->getPublishedOrdered()
            ->filter(fn (Post $post): bool => $post->id !== $context->excludePostId)
            ->map(function (Post $post) use ($context, $contextTerms, $contextTags): ?RelatedContentCandidateData {
                $matchedTerms = array_values(array_intersect(
                    $contextTerms,
                    $this->terms(
                        $post->title,
                        $post->short_description,
                        $post->seo?->focus_keyword,
                        ...$post->tags->pluck('name')->all(),
                    ),
                ));

                $sharedTags = array_values(array_intersect(
                    $contextTags,
                    $this->normalizeLabels($post->tags->pluck('name')->all()),
                ));

                $score = min(count($matchedTerms), 6);
                $score += min(count($sharedTags) * 3, 9);
                $score += 5;

                if ($context->categoryId !== null && $context->categoryId === $post->category_id) {
                    $score += 4;
                }

                if ($context->focusKeyword !== null && stripos(
                    implode(' ', [$post->title, $post->short_description, $post->seo?->focus_keyword]),
                    $context->focusKeyword
                ) !== false) {
                    $score += 4;
                }

                if ($score <= 0) {
                    return null;
                }

                return new RelatedContentCandidateData(
                    contentType: 'post',
                    id: $post->id,
                    title: $post->title,
                    slug: $post->slug,
                    url: $this->canonicalUrls->for($post),
                    score: $score,
                    matchedTerms: array_values(array_unique(array_merge($matchedTerms, $sharedTags))),
                    meta: [
                        'category_slug' => $post->category?->slug,
                        'tag_slugs' => $post->tags->pluck('slug')->all(),
                        'published_at' => $post->published_at?->toISOString(),
                    ],
                );
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<RelatedContentCandidateData>
     */
    private function scoreKnowledgeBaseEntries(InternalLinkContextData $context): array
    {
        $contextTerms = $this->terms(
            $context->title,
            $context->excerpt,
            $context->focusKeyword,
            ...$context->tagNames,
        );
        $contextTags = $this->normalizeLabels($context->tagNames);

        return KnowledgeBaseEntry::query()
            ->with('seo')
            ->where('status', KnowledgeBaseEntry::STATUS_ACTIVE)
            ->get()
            ->map(function (KnowledgeBaseEntry $entry) use ($context, $contextTerms, $contextTags): ?RelatedContentCandidateData {
                $metadataTags = is_array($entry->metadata['tags'] ?? null)
                    ? array_values(array_filter($entry->metadata['tags'], 'is_string'))
                    : [];

                $matchedTerms = array_values(array_intersect(
                    $contextTerms,
                    $this->terms(
                        $entry->title,
                        $entry->summary,
                        $entry->content_markdown,
                        ...$metadataTags,
                    ),
                ));

                $sharedTags = array_values(array_intersect(
                    $contextTags,
                    $this->normalizeLabels($metadataTags),
                ));

                $score = min(count($matchedTerms), 6);
                $score += min(count($sharedTags) * 3, 9);

                if ($context->focusKeyword !== null && stripos(
                    implode(' ', [$entry->title, $entry->summary, $entry->content_markdown]),
                    $context->focusKeyword
                ) !== false) {
                    $score += 4;
                }

                if ($context->excludePostId !== null) {
                    $linkedPostIds = array_map(
                        static fn (array $post): int => $post['id'],
                        $entry->linkedPosts(),
                    );

                    if (in_array($context->excludePostId, $linkedPostIds, true)) {
                        $score += 5;
                    }
                }

                if ($score <= 0) {
                    return null;
                }

                return new RelatedContentCandidateData(
                    contentType: 'knowledge_base_entry',
                    id: $entry->id,
                    title: $entry->title,
                    slug: $entry->slug,
                    url: $this->canonicalUrls->for($entry),
                    score: $score,
                    matchedTerms: array_values(array_unique(array_merge($matchedTerms, $sharedTags))),
                    meta: [
                        'entry_type' => $entry->entry_type,
                        'linked_posts' => $entry->linkedPosts(),
                    ],
                );
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function terms(?string ...$values): array
    {
        $terms = [];

        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $parts = preg_split('/[^a-z0-9]+/i', mb_strtolower($value)) ?: [];

            foreach ($parts as $part) {
                $part = trim($part);

                if ($part === '' || mb_strlen($part) < 3 || in_array($part, $this->stopWords(), true)) {
                    continue;
                }

                $terms[] = $part;
            }
        }

        return array_values(array_unique($terms));
    }

    /**
     * @param  list<string>  $labels
     * @return list<string>
     */
    private function normalizeLabels(array $labels): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (string $label): string => trim(mb_strtolower($label)),
            $labels,
        ))));
    }

    /**
     * @return list<string>
     */
    private function stopWords(): array
    {
        return ['and', 'the', 'for', 'with', 'that', 'this', 'from', 'into', 'your', 'about'];
    }

    private function contentTypePriority(string $contentType): int
    {
        return match ($contentType) {
            'post' => 0,
            'knowledge_base_entry' => 1,
            default => 2,
        };
    }
}
