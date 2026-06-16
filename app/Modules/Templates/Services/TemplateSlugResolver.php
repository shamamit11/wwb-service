<?php

namespace App\Modules\Templates\Services;

use App\Modules\Templates\Repositories\TemplateRepository;
use Illuminate\Support\Str;

class TemplateSlugResolver
{
    public function __construct(
        private readonly TemplateRepository $templates,
    ) {}

    public function resolve(string $name, ?string $slug = null, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($slug ?: $name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::ulid()->toBase32());
        $candidate = $baseSlug;
        $suffix = 2;

        while ($this->templates->existsBySlug($candidate, $ignoreId)) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }
}
