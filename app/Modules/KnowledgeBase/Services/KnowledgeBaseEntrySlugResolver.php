<?php

namespace App\Modules\KnowledgeBase\Services;

use App\Modules\KnowledgeBase\Repositories\KnowledgeBaseEntryRepository;
use Illuminate\Support\Str;

class KnowledgeBaseEntrySlugResolver
{
    public function __construct(
        private readonly KnowledgeBaseEntryRepository $entries,
    ) {}

    public function resolve(string $title, ?string $slug = null, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $title);
        $baseSlug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::ulid()->toBase32());
        $candidate = $baseSlug;
        $suffix = 2;

        while ($this->entries->existsBySlug($candidate, $ignoreId)) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
