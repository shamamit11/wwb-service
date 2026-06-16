# Current Task

## Task Summary

Implement an RSS-ready published content data API.

## Requested Outcome

- add an RSS query service
- add feed serialization support
- ensure latest published articles are returned in feed-friendly order

## Scope Boundaries

- in scope: service-side RSS query, feed-oriented resource shape, and an internal endpoint for generators
- out of scope: XML RSS document rendering, frontend page rendering, and block-to-HTML article rendering

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/seo.md`
- `.agent/skills/seo.md`
- `.agent/skills/laravel-api.md`

## Repository Files Inspected

- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Http/Resources/Api/V1/SitemapEntryResource.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Seo/Services/ListSitemapEntriesService.php`
- `tests/Feature/SitemapApiTest.php`

## Plan

1. Add an RSS query service that returns the latest published public posts in feed order.
2. Expose a feed-specific resource and internal endpoint for RSS generators.
3. Add focused tests, then validate with targeted tests and the full suite.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/RssFeedController.php`
- `app/Http/Resources/Api/V1/RssFeedEntryResource.php`
- `app/Modules/Seo/Services/ListRssFeedEntriesService.php`
- `routes/api.php`
- `tests/Feature/RssFeedApiTest.php`

## Validation

- `php artisan test tests/Feature/RssFeedApiTest.php tests/Feature/SitemapApiTest.php tests/Feature/PostApiTest.php` — passed
- `vendor/bin/pint --test` — passed
- `php artisan test` — passed

## Risks Or Follow-Ups

- Feed output will stay data-oriented for now; if full article bodies are required later, block rendering should be added as a separate concern rather than embedded into this API.

## Completion Notes

- Added `ListRssFeedEntriesService` to provide published public posts in latest-first feed order.
- Added an authenticated internal endpoint at `GET /api/v1/admin/feeds/rss` backed by `RssFeedEntryResource`.
- Exposed feed-oriented fields for generators: title, description, canonical link, published timestamp, last-modified timestamp, author, and category.
- Added feature coverage for auth, published-only filtering, SEO-description fallback behavior, and ordering.
