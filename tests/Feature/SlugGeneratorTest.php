<?php

namespace Tests\Feature;

use App\Support\SlugGenerator;
use Tests\TestCase;

class SlugGeneratorTest extends TestCase
{
    public function test_slug_generation_is_deterministic_for_normal_inputs(): void
    {
        $generator = app(SlugGenerator::class);

        $slug = $generator->resolve(
            source: 'How AI Agent Memory Works',
            preferredSlug: null,
            ignoreId: null,
            slugExists: fn (): bool => false,
        );

        $this->assertSame('how-ai-agent-memory-works', $slug);
    }

    public function test_slug_generation_uses_preferred_slug_when_provided(): void
    {
        $generator = app(SlugGenerator::class);

        $slug = $generator->resolve(
            source: 'Ignored Source',
            preferredSlug: 'Custom SEO Slug',
            ignoreId: null,
            slugExists: fn (): bool => false,
        );

        $this->assertSame('custom-seo-slug', $slug);
    }

    public function test_slug_generation_adds_incrementing_numeric_suffixes_until_unique(): void
    {
        $generator = app(SlugGenerator::class);

        $slug = $generator->resolve(
            source: 'Tutorial',
            preferredSlug: null,
            ignoreId: 99,
            slugExists: fn (string $candidate, ?int $ignoreId): bool => in_array($candidate, ['tutorial', 'tutorial-2', 'tutorial-3'], true) && $ignoreId === 99,
        );

        $this->assertSame('tutorial-4', $slug);
    }

    public function test_slug_generation_falls_back_to_non_empty_ulid_slug_when_source_is_blank(): void
    {
        $generator = app(SlugGenerator::class);

        $slug = $generator->resolve(
            source: '   ',
            preferredSlug: '',
            ignoreId: null,
            slugExists: fn (): bool => false,
        );

        $this->assertNotSame('', $slug);
        $this->assertMatchesRegularExpression('/^[a-z0-9]+$/', $slug);
    }
}
