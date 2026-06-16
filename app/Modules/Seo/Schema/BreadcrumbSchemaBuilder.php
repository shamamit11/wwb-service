<?php

namespace App\Modules\Seo\Schema;

use App\Models\Category;
use App\Models\Post;
use App\Modules\Seo\Services\CanonicalUrlService;

class BreadcrumbSchemaBuilder
{
    public function __construct(
        private readonly CanonicalUrlService $canonicalUrls,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forPost(Post $post): array
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        $canonical = $this->canonicalUrls->for($post) ?? "{$baseUrl}/";
        $categoryCanonical = $post->category === null ? null : $this->canonicalUrls->for($post->category);

        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => "{$baseUrl}/",
            ],
        ];

        if ($post->category !== null && $categoryCanonical !== null) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => count($items) + 1,
                'name' => $post->category->name,
                'item' => $categoryCanonical,
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => $post->title,
            'item' => $canonical,
        ];

        return [
            '@type' => 'BreadcrumbList',
            '@id' => "{$canonical}#breadcrumb",
            'itemListElement' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forCategory(Category $category): array
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        $canonical = $this->canonicalUrls->for($category) ?? "{$baseUrl}/";

        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => "{$baseUrl}/",
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $category->name,
                'item' => $canonical,
            ],
        ];

        return [
            '@type' => 'BreadcrumbList',
            '@id' => "{$canonical}#breadcrumb",
            'itemListElement' => $items,
        ];
    }
}
