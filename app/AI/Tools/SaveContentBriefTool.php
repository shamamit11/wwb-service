<?php

namespace App\AI\Tools;

use App\AI\DTO\ContentBriefResult;
use App\Models\ContentBrief;
use App\Modules\ContentBriefs\Data\CreateContentBriefData;
use App\Modules\ContentBriefs\Data\UpdateContentBriefData;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use App\Modules\ContentBriefs\Services\ContentBriefSlugResolver;

class SaveContentBriefTool
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
        private readonly ContentBriefSlugResolver $slugResolver,
    ) {}

    public function save(
        int $contentTopicId,
        ?string $primaryKeyword,
        array $secondaryKeywords,
        ?string $searchIntent,
        ContentBriefResult $result,
    ): ContentBrief {
        $existing = $this->briefs->findByTopicId($contentTopicId);
        $slug = $this->slugResolver->resolve(
            title: $result->recommendedTitle,
            slug: $result->slug,
            ignoreId: $existing?->id,
        );

        if ($existing instanceof ContentBrief) {
            return $this->briefs->update($existing, new UpdateContentBriefData(
                title: $result->recommendedTitle,
                slug: $slug,
                metaTitle: $result->metaTitle,
                metaDescription: $result->metaDescription,
                primaryKeyword: $primaryKeyword,
                secondaryKeywords: $secondaryKeywords,
                searchIntent: $searchIntent,
                outline: $result->outline,
                headings: $result->headingStructure,
                faqSuggestions: $result->faqSuggestions,
                internalLinkSuggestions: $result->internalLinkSuggestions,
                imageSuggestions: $result->imageSuggestions(),
                status: ContentBrief::STATUS_DRAFT,
            ));
        }

        return $this->briefs->create(new CreateContentBriefData(
            contentTopicId: $contentTopicId,
            title: $result->recommendedTitle,
            slug: $slug,
            metaTitle: $result->metaTitle,
            metaDescription: $result->metaDescription,
            primaryKeyword: $primaryKeyword,
            secondaryKeywords: $secondaryKeywords,
            searchIntent: $searchIntent,
            outline: $result->outline,
            headings: $result->headingStructure,
            faqSuggestions: $result->faqSuggestions,
            internalLinkSuggestions: $result->internalLinkSuggestions,
            imageSuggestions: $result->imageSuggestions(),
            status: ContentBrief::STATUS_DRAFT,
        ));
    }
}
