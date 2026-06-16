<?php

namespace App\Modules\Posts\Services;

use App\Modules\Posts\Repositories\PostRepository;
use Illuminate\Support\Str;

class PostSlugResolver
{
    public function __construct(
        private readonly PostRepository $posts,
    ) {}

    public function resolve(string $title, ?string $slug = null, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::ulid()->toBase32());
        $candidate = $baseSlug;
        $suffix = 2;

        while ($this->posts->existsBySlug($candidate, $ignoreId)) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
