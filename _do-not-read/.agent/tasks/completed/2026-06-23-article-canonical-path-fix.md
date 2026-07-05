# Task Summary

Fix article canonical URLs so published posts resolve under `/articles/{slug}/`, including structured data payloads that currently omit the `articles` path segment.

## Requested Outcome

- published post canonical URLs use `https://www.widewebblog.com/articles/{slug}/`
- article JSON-LD uses the same `/articles/{slug}/` canonical in `url`, `mainEntityOfPage`, and `@id`-derived values
- related article-facing API payloads that expose canonical URLs stay consistent

## Scope Boundaries

- in scope: service-side canonical URL generation for posts, schema payload generation, and focused test updates
- out of scope: frontend route changes and unrelated canonical behavior for pages, categories, or knowledge base entries

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/skills/seo.md`
- `.agent/knowledge-base/seo.md`
- `.agent/PROJECT-CONTEXT.md`

## Repository Files Inspected

- `app/Modules/Seo/Services/CanonicalUrlService.php`
- `app/Modules/Seo/Services/GenerateSchemaPayloadService.php`
- `app/Modules/Seo/Schema/ArticleSchemaBuilder.php`
- `app/Modules/Seo/Schema/BreadcrumbSchemaBuilder.php`
- `app/Http/Resources/Api/V1/PublicPostDetailResource.php`
- `app/Http/Resources/Api/V1/PublicSeoMetadataResource.php`
- `app/Http/Resources/Api/V1/SeoMetadataResource.php`
- `routes/api.php`
- `tests/Feature/CanonicalUrlServiceTest.php`
- `tests/Feature/SeoMetadataApiTest.php`
- `tests/Feature/RssFeedApiTest.php`
- `tests/Feature/SitemapApiTest.php`

## Plan

1. Update post canonical generation to emit `/articles/{slug}/`.
2. Add or update focused tests covering canonical URLs and article schema payloads.
3. Run the targeted test set and record outcomes.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Modules/Seo/Services/CanonicalUrlService.php`
- `tests/Feature/CanonicalUrlServiceTest.php`
- `tests/Feature/SeoMetadataApiTest.php`
- `tests/Feature/RssFeedApiTest.php`
- `tests/Feature/SitemapApiTest.php`
- `tests/Feature/PublicPostDetailApiTest.php`

## Validation

- `php artisan test tests/Feature/CanonicalUrlServiceTest.php tests/Feature/SeoMetadataApiTest.php tests/Feature/RssFeedApiTest.php tests/Feature/SitemapApiTest.php tests/Feature/PublicPostDetailApiTest.php`
- result: passed, 14 tests / 100 assertions

## Risks Or Follow-Ups

- stored canonical overrides that already omit `/articles/` are editorial data and will not be auto-rewritten by this change

## Completion Notes

- updated published post canonical generation to use `/articles/{slug}/`
- verified public post detail schema now emits `/articles/{slug}/` in breadcrumb and article graph nodes
