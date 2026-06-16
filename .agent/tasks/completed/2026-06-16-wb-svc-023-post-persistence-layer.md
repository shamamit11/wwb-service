# Current Task

## Task Summary

Implement post persistence with relationships and publish-state fields.

## Requested Outcome

- add posts migration
- add post model
- add repositories

## Scope Boundaries

- in scope: post schema, post model relationships and casts, repository contracts and implementations, admin/public-safe query patterns, task tracking, and tests
- out of scope: post-block persistence, post APIs, publish/schedule endpoints, SEO metadata persistence, sibling repositories, and rendered content behavior

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/skills/database.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/module-map.md`
- `docs/DATABASE_DESIGN.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `app/Models/User.php`
- `app/Models/Category.php`
- `app/Models/Media.php`
- `app/Models/Tag.php`
- `app/Models/Template.php`
- `app/Modules/Categories/Repositories/CategoryRepository.php`
- `app/Modules/Categories/Repositories/EloquentCategoryRepository.php`
- `app/Modules/Templates/Repositories/TemplateRepository.php`
- `app/Modules/Templates/Repositories/EloquentTemplateRepository.php`
- `database/migrations/2026_06_16_170100_create_post_tags_table.php`

## Plan

1. Add the `posts` schema with the documented lifecycle fields, foreign keys, soft deletes, and public-read indexes.
2. Add the `Post` model with relationships to author, category, template, featured media, and tags plus status/visibility constants and casts.
3. Add repository contracts and Eloquent queries for admin lookup and public-safe published retrieval patterns.
4. Add focused schema/repository tests and validate with migrations and the Laravel test suite.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Models/Category.php`
- `app/Models/Media.php`
- `app/Models/Post.php`
- `app/Models/Tag.php`
- `app/Models/Template.php`
- `app/Modules/Posts/Data/CreatePostData.php`
- `app/Modules/Posts/Data/UpdatePostData.php`
- `app/Modules/Posts/Repositories/EloquentPostRepository.php`
- `app/Modules/Posts/Repositories/PostRepository.php`
- `app/Providers/AppServiceProvider.php`
- `database/migrations/2026_06_16_190000_create_posts_table.php`
- `database/migrations/2026_06_16_190100_add_post_foreign_key_to_post_tags_table.php`
- `tests/Feature/MediaUsageApiTest.php`
- `tests/Feature/PostRepositoryTest.php`
- `tests/Feature/TagRepositoryTest.php`

## Validation

- `php artisan migrate`
- `php artisan test tests/Feature/PostRepositoryTest.php`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- The post persistence layer is in place, but post-block persistence and publish-state orchestration still need to be layered on top of these repository contracts in later tasks.

## Completion Notes

- Summary: Added the post persistence layer with lifecycle fields, relationship-aware model wiring, repository CRUD/query patterns for admin and published-only reads, and the missing `post_tags.post_id` foreign key.
- Changed files: Added the `Post` model, post create/update DTOs, the post repository contract and Eloquent implementation, the posts migration plus a follow-up foreign key migration for `post_tags`, inverse relationships on related models, the repository binding in `AppServiceProvider`, and focused repository coverage with compatibility updates to existing media/tag tests.
- Validation run: `php artisan migrate`, `php artisan test tests/Feature/PostRepositoryTest.php`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: Public-safe repository queries currently gate on `status = published`, `visibility = public`, and non-null `published_at`, but future SEO or post-block concerns may require additional eager-loaded relations or query specialization.
- Follow-ups: Add a post slug resolver and higher-level post services when the post API task starts, and align post-block persistence and publish-state transitions with these repository contracts rather than bypassing them.
