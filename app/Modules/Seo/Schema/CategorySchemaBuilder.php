<?php

namespace App\Modules\Seo\Schema;

use App\Models\Category;
use App\Modules\Seo\Services\CanonicalUrlService;

class CategorySchemaBuilder
{
    public function __construct(
        private readonly CanonicalUrlService $canonicalUrls,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Category $category): array
    {
        $canonical = $this->canonicalUrls->for($category) ?? rtrim((string) config('app.url'), '/').'/';
        $metadata = $category->seo;

        $schema = [
            '@type' => $metadata?->schema_type ?: 'CollectionPage',
            '@id' => "{$canonical}#webpage",
            'name' => $metadata?->meta_title ?: $category->name,
            'description' => $metadata?->meta_description ?: $category->description,
            'url' => $canonical,
            'isPartOf' => [
                '@id' => rtrim((string) config('app.url'), '/').'/#website',
            ],
            'breadcrumb' => [
                '@id' => "{$canonical}#breadcrumb",
            ],
        ];

        return $this->mergeOverrides($schema, $metadata?->schema_payload);
    }

    /**
     * @param  array<string, mixed>|null  $overrides
     * @return array<string, mixed>
     */
    private function mergeOverrides(array $schema, ?array $overrides): array
    {
        if ($overrides === null || $overrides === []) {
            return $schema;
        }

        return array_replace_recursive($schema, $overrides);
    }
}
