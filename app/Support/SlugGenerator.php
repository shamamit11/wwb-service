<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Str;

class SlugGenerator
{
    /**
     * @param  Closure(string, int|null): bool  $slugExists
     */
    public function resolve(
        string $source,
        ?string $preferredSlug,
        ?int $ignoreId,
        Closure $slugExists,
    ): string {
        $baseSlug = Str::slug($preferredSlug ?: $source);
        $baseSlug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::ulid()->toBase32());
        $candidate = $baseSlug;
        $suffix = 2;

        while ($slugExists($candidate, $ignoreId)) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
