<?php

namespace App\Modules\Seo\Services;

use App\Models\Category;
use App\Models\Post;
use App\Modules\Seo\Schema\ArticleSchemaBuilder;
use App\Modules\Seo\Schema\BreadcrumbSchemaBuilder;
use App\Modules\Seo\Schema\CategorySchemaBuilder;
use App\Modules\Seo\Schema\FaqSchemaBuilder;
use App\Modules\Seo\Schema\OrganizationSchemaBuilder;
use App\Modules\Seo\Schema\WebsiteSchemaBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GenerateSchemaPayloadService
{
    public function __construct(
        private readonly ResolveSeoableTargetService $resolver,
        private readonly OrganizationSchemaBuilder $organization,
        private readonly WebsiteSchemaBuilder $website,
        private readonly BreadcrumbSchemaBuilder $breadcrumbs,
        private readonly ArticleSchemaBuilder $articles,
        private readonly CategorySchemaBuilder $categories,
        private readonly FaqSchemaBuilder $faqs,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(string $seoableType, int $seoableId): array
    {
        [$seoable, $normalizedType] = $this->resolver->handle($seoableType, $seoableId);

        return match ($normalizedType) {
            'post' => $this->forPost($seoable->loadMissing(['author', 'category', 'tags', 'blocks', 'seo.ogImageMedia'])),
            'category' => $this->forCategory($seoable->loadMissing(['seo'])),
            default => throw new NotFoundHttpException('Schema target not found.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function forPost(Post $post): array
    {
        $graph = [
            $this->organization->build(),
            $this->website->build(),
            $this->breadcrumbs->forPost($post),
            $this->articles->build($post),
        ];

        $faq = $this->faqs->build($post);

        if ($faq !== null) {
            $graph[] = $faq;
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function forCategory(Category $category): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->organization->build(),
                $this->website->build(),
                $this->breadcrumbs->forCategory($category),
                $this->categories->build($category),
            ],
        ];
    }
}
