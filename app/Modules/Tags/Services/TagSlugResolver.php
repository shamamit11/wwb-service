<?php

namespace App\Modules\Tags\Services;

use App\Modules\Tags\Repositories\TagRepository;
use Illuminate\Support\Str;

class TagSlugResolver
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {}

    public function resolve(string $name, ?string $slug = null, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::ulid()->toBase32());
        $candidate = $baseSlug;
        $suffix = 2;

        while ($this->tags->existsBySlug($candidate, $ignoreId)) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
