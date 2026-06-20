<?php

namespace App\Modules\Pages\Services;

use App\Models\Page;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FindPublicPageBySlugService
{
    public function handle(string $slug): Page
    {
        $page = Page::query()
            ->with('seo.ogImageMedia')
            ->where('slug', $slug)
            ->where('status', Page::STATUS_PUBLISHED)
            ->where('visibility', Page::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->first();

        if ($page === null) {
            throw new NotFoundHttpException;
        }

        return $page;
    }
}
