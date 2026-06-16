# Current Task

## Task Summary

Implement template CRUD APIs plus preview and seed-post payload support.

## Requested Outcome

- add template CRUD endpoints
- add preview endpoint
- add seed-post payload endpoint

## Scope Boundaries

- in scope: template controllers, requests, resources, services, routes, preview payload generation, seed-post payload generation, tests, task tracking
- out of scope: visual template editing, rendered HTML previews, post persistence from templates, sibling repositories, and admin UI work

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/ARCHITECTURE.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/api-contracts.md`
- `.agent/skills/template-engine.md`
- `docs/TEMPLATE_ENGINE.md`
- `docs/OPENAPI_SPEC.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Http/Controllers/Api/V1/Admin/CategoryController.php`
- `app/Http/Controllers/Api/V1/Admin/TagController.php`
- `app/Http/Requests/Api/V1/Admin/StoreCategoryRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateCategoryRequest.php`
- `app/Http/Resources/Api/ApiResource.php`
- `app/Http/Resources/Api/V1/CategoryResource.php`
- `app/Modules/Categories/Services/CreateCategoryService.php`
- `app/Models/Template.php`
- `app/Models/TemplateBlock.php`
- `app/Modules/Templates/Repositories/TemplateRepository.php`
- `app/Modules/Templates/Repositories/EloquentTemplateRepository.php`

## Plan

1. Mirror the existing admin API conventions for templates with routes, controllers, requests, DTO conversion, services, and resources.
2. Add preview and seed-payload services that derive deterministic placeholder content from ordered template blocks and template metadata.
3. Add feature coverage for CRUD, auth/error behavior, preview payloads, and seed-post payloads.
4. Validate with targeted and full Laravel tests, then update task records.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Http/Controllers/Api/V1/Admin/TemplateController.php`
- `app/Http/Requests/Api/V1/Admin/BuildTemplatePreviewRequest.php`
- `app/Http/Requests/Api/V1/Admin/BuildTemplateSeedPostRequest.php`
- `app/Http/Requests/Api/V1/Admin/Concerns/InteractsWithTemplateData.php`
- `app/Http/Requests/Api/V1/Admin/StoreTemplateRequest.php`
- `app/Http/Requests/Api/V1/Admin/UpdateTemplateRequest.php`
- `app/Http/Resources/Api/V1/TemplateBlockResource.php`
- `app/Http/Resources/Api/V1/TemplatePreviewResource.php`
- `app/Http/Resources/Api/V1/TemplateResource.php`
- `app/Http/Resources/Api/V1/TemplateSeedPostPayloadResource.php`
- `app/Models/Template.php`
- `app/Models/TemplateBlock.php`
- `app/Modules/Templates/Data/TemplatePayloadContextData.php`
- `app/Modules/Templates/Repositories/EloquentTemplateRepository.php`
- `app/Modules/Templates/Repositories/TemplateRepository.php`
- `app/Modules/Templates/Services/BuildTemplatePreviewPayloadService.php`
- `app/Modules/Templates/Services/BuildTemplateSeedPostPayloadService.php`
- `app/Modules/Templates/Services/CreateTemplateService.php`
- `app/Modules/Templates/Services/DeleteTemplateService.php`
- `app/Modules/Templates/Services/ListAdminTemplatesService.php`
- `app/Modules/Templates/Services/TemplatePayloadFactory.php`
- `app/Modules/Templates/Services/TemplateSlugResolver.php`
- `app/Modules/Templates/Services/UpdateTemplateService.php`
- `routes/api.php`
- `tests/Feature/TemplateApiTest.php`

## Validation

- `php artisan test tests/Feature/TemplateApiTest.php`
- `php artisan test tests/Feature/TemplateRepositoryTest.php`
- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

## Risks Or Follow-Ups

- Preview and seed payloads will be structural payloads only for now; rendered public preview behavior will still need a later post-block rendering layer.

## Completion Notes

- Summary: Added authenticated admin template CRUD endpoints plus preview and seed-post payload endpoints, with deterministic placeholder payload generation based on ordered template blocks and template config.
- Changed files: Added the admin template controller, form requests, API resources, payload context DTO, template services for list/create/update/delete/preview/seed behavior, expanded template model and repository capabilities, registered admin routes, and added feature coverage for CRUD and payload generation.
- Validation run: `php artisan test tests/Feature/TemplateApiTest.php`, `php artisan test tests/Feature/TemplateRepositoryTest.php`, `php artisan test`, `./vendor/bin/pint --test`, and `./vendor/bin/phpstan analyse` all passed.
- Risks: Preview and seed-post payloads currently generate structured placeholder content only; they do not yet represent final public rendering or post-block persistence semantics.
- Follow-ups: When post and post-block modules land, align the seed-post payload shape directly to post creation contracts and replace placeholder preview output with rendering-aware preview generation where needed.
