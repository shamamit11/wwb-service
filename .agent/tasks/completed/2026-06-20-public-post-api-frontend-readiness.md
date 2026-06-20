# Task Record

## Task Summary

Audit the public post content API used by `fe` and implement any missing public post endpoints or response fields needed to remove frontend-local article fallback data.

## Requested Outcome

- Verify whether public post detail and search endpoints already exist.
- Ensure public post detail by slug is frontend-ready.
- Ensure public post search is available and safe for empty queries.
- Return enough structured post fields for article detail, cards, search results, related posts, and SEO.
- Add or update focused feature coverage for public post detail and search behavior.

## Scope Boundaries

- In scope: public service API routes, requests, services, query behavior, resources, tests, and API docs touched by the contract.
- Out of scope: frontend code changes in `../fe`, admin UI changes, and post persistence model refactors.

## Cross-App Context

- Sibling context is required because this task is a public frontend-impacting API contract audit.
- Per repository rules, the task uses only the user-provided frontend needs and does not scan `../fe`.

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/seo.md`
- `.agent/TESTING.md`
- `.agent/skills/service-testing-matrix.md`
- `.agent/COMMANDS.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Http/Controllers/Api/V1/Public/PostController.php`
- `app/Http/Controllers/Api/V1/Public/SearchController.php`
- `app/Http/Requests/Api/V1/Public/ListPublicPostsRequest.php`
- `app/Http/Requests/Api/V1/Public/SearchPublicPostsRequest.php`
- `app/Http/Resources/Api/V1/PublicPostSummaryResource.php`
- `app/Http/Resources/Api/V1/PublicPostDetailResource.php`
- `app/Http/Resources/Api/V1/PublicSeoMetadataResource.php`
- `app/Http/Resources/Api/V1/PublicMediaResource.php`
- `app/Http/Resources/Api/V1/PublicPostBlockResource.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Models/Post.php`
- `app/Modules/Posts/Data/PublicPostFiltersData.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Services/ListPublicPostsService.php`
- `app/Modules/Posts/Services/FindPublishedPostBySlugService.php`
- `tests/Feature/PublicFrontendApiTest.php`
- `docs/OPENAPI_SPEC.md`

## Findings Before Changes

- `GET /api/v1/public/posts` already exists and supports `search`, `category`, `tag`, `sort`, and `per_page`.
- `GET /api/v1/public/posts/{slug}` already exists.
- `GET /api/v1/public/search` already exists as a thin alias over the public post listing service.
- Existing public post summary/detail responses did not include enough frontend-friendly fields yet:
  - missing author on public post responses
  - missing read-time display field
  - missing derived body/content field on post detail
  - missing related-posts payload on post detail
  - search did not match post block content/body
  - `/api/v1/public/search` required non-empty `q`, so empty-query handling was not safe

## Plan

1. Extend public post query behavior to support content/body search and safe empty-query handling.
2. Expand public post summary and detail resources with author, featured image convenience, read time, content aggregate, and related posts.
3. Add focused feature coverage for public detail-by-slug, missing slug, search results, and empty search results.
4. Run narrow validation and update task notes with results and residual gaps.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Requests/Api/V1/Public/SearchPublicPostsRequest.php`
- `app/Http/Resources/Api/V1/PublicPostSummaryResource.php`
- `app/Http/Resources/Api/V1/PublicPostDetailResource.php`
- `app/Modules/Posts/Data/PublicPostFiltersData.php`
- `app/Modules/Posts/Services/ListPublicPostsService.php`
- `app/Modules/Posts/Services/FindPublishedPostBySlugService.php`
- `tests/Feature/PublicFrontendApiTest.php`
- `docs/OPENAPI_SPEC.md`

## Validation

- Passed: `php artisan test tests/Feature/PublicFrontendApiTest.php`
- Passed: `vendor/bin/pint app/Http/Requests/Api/V1/Public/SearchPublicPostsRequest.php app/Http/Resources/Api/V1/PublicPostSummaryResource.php app/Http/Resources/Api/V1/PublicPostDetailResource.php app/Modules/Posts/Data/PublicPostFiltersData.php app/Modules/Posts/Services/ListPublicPostsService.php app/Modules/Posts/Services/FindPublishedPostBySlugService.php tests/Feature/PublicFrontendApiTest.php`

## Risks Or Follow-Ups

- Post detail now exposes a derived `content` and `content_markdown` aggregate built from ordered post blocks. This is suitable for frontend fallback-free rendering, but it is still derived from block storage rather than a first-class persisted article-body column.
- Related posts currently use the simple rule requested: same category, exclude current post, newest first, limit 3. There is no relevance scoring beyond that.

## Completion Notes

- Existing public endpoints were kept and made frontend-ready rather than introducing new route variants.
- Public search now supports both `/api/v1/public/search?q=...` and `/api/v1/public/posts?search=...`, with block-content matching added to the shared query path.
