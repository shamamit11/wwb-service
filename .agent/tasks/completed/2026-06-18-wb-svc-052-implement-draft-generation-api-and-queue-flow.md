# Task Summary

Implement `WB-SVC-052 — draft generation API and queue flow` so admins can queue blog draft generation from approved content briefs and track the work through `ai_jobs`.

## Requested Outcome

- add `POST /api/v1/admin/content-briefs/{id}/generate-draft`
- add `GenerateBlogDraftJob`
- create queued AI job records before dispatch
- reuse the same retry-safe post creation flow from `WB-SVC-051`
- support retry dispatch for failed blog writer jobs
- validate with focused tests and `php artisan test`

## Scope Boundaries

- in scope: admin API endpoint, request validation, queued orchestration, AI job lifecycle updates, retry dispatch, conflict handling, tests
- out of scope: admin UI, public frontend work, publishing flow, non-blog-writer retry orchestration beyond current needs

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/skills/queue-scheduler.md`

## Repository Files Inspected

- `routes/api.php`
- `bootstrap/app.php`
- `app/Jobs/AI/DiscoverContentTopicsJob.php`
- `app/Http/Controllers/Api/V1/Admin/AiJobController.php`
- `app/Http/Controllers/Api/V1/Admin/ContentBriefController.php`
- `app/Http/Controllers/Api/V1/Admin/ContentTopicController.php`
- `app/Http/Requests/Api/V1/Admin/UpdateContentBriefRequest.php`
- `app/Http/Resources/Api/V1/AiJobResource.php`
- `app/Http/Resources/Api/V1/ContentBriefResource.php`
- `app/Http/Resources/Api/V1/PostResource.php`
- `app/Modules/Ai/Data/CreateAiJobData.php`
- `app/Modules/Ai/Data/DiscoverContentTopicsData.php`
- `app/Modules/Ai/Repositories/AiJobRepository.php`
- `app/Modules/Ai/Repositories/EloquentAiJobRepository.php`
- `app/Modules/Ai/Services/ReadAiJobService.php`
- `app/Modules/Ai/Services/RetryAiJobService.php`
- `app/Modules/Ai/Services/RunTopicDiscoveryService.php`
- `app/Modules/Posts/Exceptions/BlogDraftGenerationNotAllowedException.php`
- `app/Modules/Posts/Services/GenerateBlogDraftFromBriefService.php`
- `tests/Feature/AiJobApiTest.php`
- `tests/Feature/BlogWriterAgentTest.php`
- `tests/Feature/ContentBriefApiTest.php`

## Plan

1. Add request and API controller wiring for `generate-draft`.
2. Add queued orchestration services and `GenerateBlogDraftJob` using up-front `ai_jobs` creation.
3. Update `BlogWriterAgent` and retry handling so queued runs reuse existing `ai_jobs` rows and retries dispatch work.
4. Add focused API and job tests for approval gating, queued tracking, retry safety, and post draft persistence.
5. Run targeted tests, then `php artisan test`, and record any non-code blockers.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/AI/Agents/BlogWriterAgent.php`
- `app/Jobs/AI/GenerateBlogDraftJob.php`
- `app/Http/Controllers/Api/V1/Admin/ContentBriefController.php`
- `app/Http/Requests/Api/V1/Admin/GenerateBlogDraftRequest.php`
- `app/Modules/Ai/Data/QueueBlogDraftGenerationData.php`
- `app/Modules/Ai/Services/QueueBlogDraftGenerationService.php`
- `app/Modules/Ai/Services/RunBlogDraftGenerationService.php`
- `app/Modules/Ai/Services/RetryAiJobService.php`
- `app/Modules/Posts/Services/GenerateBlogDraftFromBriefService.php`
- `bootstrap/app.php`
- `routes/api.php`
- `tests/Feature/AiJobApiTest.php`
- `tests/Feature/ContentBriefApiTest.php`
- `tests/Feature/GenerateBlogDraftJobTest.php`

## Validation

- `php -l app/Jobs/AI/GenerateBlogDraftJob.php`
- `php -l app/Http/Requests/Api/V1/Admin/GenerateBlogDraftRequest.php`
- `php -l app/Modules/Ai/Services/QueueBlogDraftGenerationService.php`
- `php -l app/Modules/Ai/Services/RunBlogDraftGenerationService.php`
- `php -l tests/Feature/GenerateBlogDraftJobTest.php`
- `php -l tests/Feature/ContentBriefApiTest.php`
- `php -l tests/Feature/AiJobApiTest.php`
- `php artisan test tests/Feature/ContentBriefApiTest.php`
- `php artisan test tests/Feature/AiJobApiTest.php`
- `php artisan test tests/Feature/GenerateBlogDraftJobTest.php`
- `php artisan test tests/Feature/BlogWriterAgentTest.php`
- `php artisan test tests/Feature/AiContentAgentContractsTest.php`
- `php artisan test` failed in `Tests\Feature\ActivityLogTest::test_editorial_mutations_are_recorded_with_curated_audit_payloads` because storage credentials could not be retrieved from the instance metadata service for an external write path

## Risks Or Follow-Ups

- queued draft generation still needs explicit `category_id`; only `author_user_id` safely defaults
- full-suite validation is currently blocked by an environment storage credential issue outside the new draft-generation flow

## Completion Notes

- completed `WB-SVC-052` implementation
- admins can now queue draft generation from approved briefs, see the queued `ai_jobs` record immediately, and retry failed `blog_writer` jobs without creating duplicate posts
