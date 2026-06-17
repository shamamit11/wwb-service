<?php

namespace App\Modules\Seo\Services;

use App\Models\Category;
use App\Models\KnowledgeBaseEntry;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveSeoableTargetService
{
    /**
     * @return array{0: Model, 1: string}
     */
    public function handle(string $seoableType, int $seoableId): array
    {
        [$className, $normalizedType] = match ($seoableType) {
            'post', 'posts' => [Post::class, 'post'],
            'category', 'categories' => [Category::class, 'category'],
            'page', 'pages' => [Page::class, 'page'],
            'knowledge-base', 'knowledge_base', 'knowledge-base-entry', 'knowledge_base_entry' => [KnowledgeBaseEntry::class, 'knowledge_base_entry'],
            default => throw new NotFoundHttpException('SEO target not found.'),
        };

        $model = $className::query()->find($seoableId);

        if ($model === null) {
            throw new NotFoundHttpException('SEO target not found.');
        }

        return [$model, $normalizedType];
    }
}
