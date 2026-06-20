# Task Record

## Task Summary

Implement a proper singleton `site-settings` service surface for site-wide footer management, including schema, admin API, and public API.

## Requested Outcome

- Add persistent site settings schema for footer content.
- Expose admin read/update endpoints to manage site settings.
- Expose a public endpoint for frontend footer consumption.
- Keep the contract site-wide and singleton-based, not page-based.
- Add focused feature coverage for bootstrapping, update, validation, and public read.

## Scope Boundaries

- In scope: schema, model, repository, DTO, services, requests, resources, routes, provider bindings, docs, and feature tests for site settings.
- Out of scope: frontend implementation in `fe`, page-system refactors, and broader publication settings beyond the footer/site-settings payload needed now.

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/agents/CODEX.md`
- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/api-standards.md`
- `.agent/skills/laravel-api.md`
- `.agent/skills/api-contracts.md`
- `.agent/TESTING.md`
- `.agent/skills/service-testing-matrix.md`

## Repository Files Inspected

- `routes/api.php`
- `app/Models/Homepage.php`
- `app/Models/AboutPage.php`
- `app/Models/ContactPage.php`
- `app/Http/Controllers/Api/V1/Admin/HomepageController.php`
- `app/Http/Requests/Api/V1/Admin/UpdateHomepageRequest.php`
- `app/Http/Resources/Api/V1/HomepageResource.php`
- `app/Http/Resources/Api/V1/PublicHomeResource.php`
- `app/Http/Resources/Api/V1/PublicAboutPageResource.php`
- `app/Http/Resources/Api/V1/PublicContactPageResource.php`
- `app/Modules/Homepage/Repositories/HomepageRepository.php`
- `app/Modules/Homepage/Repositories/EloquentHomepageRepository.php`
- `app/Modules/Homepage/Services/ReadHomepageService.php`
- `app/Modules/Homepage/Services/UpdateHomepageService.php`
- `app/Modules/Homepage/Data/UpdateHomepageData.php`
- `tests/Feature/HomepageApiTest.php`
- `tests/Feature/PublicAboutPageApiTest.php`
- `tests/Feature/ContactPageApiTest.php`

## Plan

1. Add a `site_settings` singleton persistence module and model default payload for footer settings.
2. Add admin/public controllers, requests, resources, routes, provider bindings, and docs using the existing singleton pattern.
3. Add focused feature tests for admin and public site settings behavior.
4. Run targeted tests and formatting, then update completion notes.

## Changed Files

- `.agent/tasks/current-task.md`
- `app/Models/SiteSettings.php`
- `app/Modules/SiteSettings/Data/UpdateSiteSettingsData.php`
- `app/Modules/SiteSettings/Repositories/SiteSettingsRepository.php`
- `app/Modules/SiteSettings/Repositories/EloquentSiteSettingsRepository.php`
- `app/Modules/SiteSettings/Services/ReadSiteSettingsService.php`
- `app/Modules/SiteSettings/Services/BuildPublicSiteSettingsService.php`
- `app/Modules/SiteSettings/Services/UpdateSiteSettingsService.php`
- `app/Http/Requests/Api/V1/Admin/UpdateSiteSettingsRequest.php`
- `app/Http/Resources/Api/V1/SiteSettingsResource.php`
- `app/Http/Resources/Api/V1/PublicSiteSettingsResource.php`
- `app/Http/Controllers/Api/V1/Admin/SiteSettingsController.php`
- `app/Http/Controllers/Api/V1/Public/SiteSettingsController.php`
- `app/Providers/AppServiceProvider.php`
- `routes/api.php`
- `database/migrations/2026_06_20_170000_create_site_settings_table.php`
- `tests/Feature/SiteSettingsApiTest.php`
- `tests/Feature/PublicSiteSettingsApiTest.php`
- `docs/OPENAPI_SPEC.md`

## Validation

- Passed: `php artisan test tests/Feature/SiteSettingsApiTest.php tests/Feature/PublicSiteSettingsApiTest.php`
- Passed: `vendor/bin/pint app/Models/SiteSettings.php app/Modules/SiteSettings app/Http/Requests/Api/V1/Admin/UpdateSiteSettingsRequest.php app/Http/Resources/Api/V1/SiteSettingsResource.php app/Http/Resources/Api/V1/PublicSiteSettingsResource.php app/Http/Controllers/Api/V1/Admin/SiteSettingsController.php app/Http/Controllers/Api/V1/Public/SiteSettingsController.php app/Providers/AppServiceProvider.php routes/api.php tests/Feature/SiteSettingsApiTest.php tests/Feature/PublicSiteSettingsApiTest.php database/migrations/2026_06_20_170000_create_site_settings_table.php`

## Risks Or Follow-Ups

- `footer.social_links[*].url` and `footer.legal_links[*].url` intentionally accept generic link strings instead of strict URL validation so `mailto:` and relative frontend routes can be stored cleanly.
- The first version only models the site-wide footer payload. If broader publication settings are needed later, this singleton can be extended additively without changing the route surface.

## Completion Notes

- Added a new singleton `site-settings` module with admin read/update and public read endpoints.
- The stored payload is intentionally narrow and footer-focused for the current frontend need: brand copy, social links, and legal links.
