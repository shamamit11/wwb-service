# Current Task

## Task Summary

Stop JSON-LD, canonical SEO URLs, and public media URLs from exposing the service host and use the public frontend/media domains instead.

## Requested Outcome

- structured data and SEO/public page URLs should emit `https://www.widewebblog.com` instead of the service host
- public media URLs should emit `https://media.widewebblog.com`
- SEO URL generation should support public frontend/media domains distinct from the backend service URL

## Scope Boundaries

- in scope: service-side SEO URL/config generation, public media URL config, and targeted tests
- out of scope: frontend app changes, domain selection/product decision between the two public domains

## Context Files Loaded

- `.agent/INDEX.md`
- `.agent/tasks/current-task.md`
- `.agent/agents/SHARED-INSTRUCTIONS.md`
- `.agent/skills/seo.md`
- `.agent/knowledge-base/seo.md`
- `.agent/knowledge-base/product.md`

## Repository Files Inspected

- `app/Modules/Seo/Services/GenerateSchemaPayloadService.php`
- `app/Modules/Seo/Services/CanonicalUrlService.php`
- `app/Modules/Seo/Schema/ArticleSchemaBuilder.php`
- `app/Modules/Seo/Schema/BreadcrumbSchemaBuilder.php`
- `app/Modules/Seo/Schema/CategorySchemaBuilder.php`
- `app/Modules/Seo/Schema/FaqSchemaBuilder.php`
- `app/Modules/Seo/Schema/OrganizationSchemaBuilder.php`
- `app/Modules/Seo/Schema/WebsiteSchemaBuilder.php`
- `config/filesystems.php`
- `app/Http/Resources/Api/V1/PublicSeoMetadataResource.php`
- `app/Http/Resources/Api/V1/SeoMetadataResource.php`
- `config/app.php`
- `tests/Feature/SchemaDataApiTest.php`
- `tests/Feature/PublicFrontendApiTest.php`

## Plan

1. Add a dedicated public/frontend base URL config for SEO outputs.
2. Update canonical and JSON-LD builders to use that config instead of `app.url`.
3. Adjust focused tests and run the relevant feature tests.

## Changed Files

- `.agent/tasks/current-task.md`
- `.env`
- `.env.example`
- `config/app.php`
- `config/filesystems.php`
- `app/Console/Commands/RewritePublicContentHostsCommand.php`
- `app/Console/Commands/ReportPublicHostUsageCommand.php`
- `app/Console/Commands/RewriteSeoCanonicalHostCommand.php`
- `app/Http/Resources/Api/V1/PublicSeoMetadataResource.php`
- `app/Http/Resources/Api/V1/SeoMetadataResource.php`
- `app/Modules/Seo/Services/CanonicalUrlService.php`
- `app/Modules/Seo/Schema/ArticleSchemaBuilder.php`
- `app/Modules/Seo/Schema/BreadcrumbSchemaBuilder.php`
- `app/Modules/Seo/Schema/CategorySchemaBuilder.php`
- `app/Modules/Seo/Schema/FaqSchemaBuilder.php`
- `app/Modules/Seo/Schema/OrganizationSchemaBuilder.php`
- `app/Modules/Seo/Schema/WebsiteSchemaBuilder.php`
- `tests/Feature/CanonicalUrlServiceTest.php`
- `tests/Feature/PageApiTest.php`
- `tests/Feature/PublicFrontendApiTest.php`
- `tests/Feature/ReportPublicHostUsageCommandTest.php`
- `tests/Feature/RewritePublicContentHostsCommandTest.php`
- `tests/Feature/RewriteSeoCanonicalHostCommandTest.php`
- `tests/Feature/RssFeedApiTest.php`
- `tests/Feature/SchemaDataApiTest.php`
- `tests/Feature/SeoMetadataApiTest.php`
- `tests/Feature/SitemapApiTest.php`

## Validation

- `php artisan test tests/Feature/SchemaDataApiTest.php` passed
- `php artisan test tests/Feature/CanonicalUrlServiceTest.php` passed
- `php artisan test tests/Feature/PublicFrontendApiTest.php` passed
- `php artisan test tests/Feature/RewriteSeoCanonicalHostCommandTest.php` passed
- `php artisan test tests/Feature/SchemaDataApiTest.php tests/Feature/CanonicalUrlServiceTest.php` passed
- `php artisan test tests/Feature/CanonicalUrlServiceTest.php tests/Feature/SeoMetadataApiTest.php` passed
- `php artisan test tests/Feature/PublicFrontendApiTest.php tests/Feature/SitemapApiTest.php tests/Feature/RssFeedApiTest.php tests/Feature/PageApiTest.php` passed
- `php artisan test tests/Feature/RewritePublicContentHostsCommandTest.php` passed
- `php artisan test tests/Feature/RewriteSeoCanonicalHostCommandTest.php tests/Feature/PublicFrontendApiTest.php tests/Feature/SeoMetadataApiTest.php` passed
- `php artisan test tests/Feature/ReportPublicHostUsageCommandTest.php` passed
- `php artisan test tests/Feature/RewritePublicContentHostsCommandTest.php tests/Feature/RewriteSeoCanonicalHostCommandTest.php` passed

## Risks Or Follow-Ups

- product still needs to choose the preferred primary public domain if both should not remain valid
- deployment/config management must set `FRONTEND_URL` in environments where `APP_URL` points at the service host
- deployment/config management must set `R2_URL` to the public media domain in environments using Cloudflare R2
- saved `seo_metadata.canonical_url` overrides still need a one-time backfill if they were stored with the service host
- stored homepage/about/footer config URLs still need a one-time backfill if they were saved with the service host
- run the reporting command first if you want a concrete list of remaining DB fields before applying rewrites

## Completion Notes

- added a dedicated frontend/public URL config for SEO outputs
- canonical URL generation and JSON-LD website/organization/breadcrumb/article/category/faq builders now use the public frontend origin instead of the service origin
- canonical URLs stored with the service host are now normalized to the frontend host when serialized through SEO/public/admin resources
- local/public disk URL config now prefers the frontend origin, and R2 public URL is configured separately for media
- focused tests now cover split service/frontend hosts and confirm canonical overrides still win
- added a command to rewrite persisted canonical URL origins for existing SEO metadata records
- added a second command to rewrite stored homepage/about/site-settings URLs, routing site links to the frontend host and media links to the R2 public host
- added a read-only reporting command that enumerates remaining DB fields still pointing at the old service host
