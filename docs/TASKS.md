# Service Tasks: Wide Web Blog

## Document Purpose

This document defines the implementation backlog for the `service` repository only.

It covers:

- backend APIs
- business logic
- database work
- media service
- SEO metadata APIs
- knowledge base APIs
- template APIs
- topic APIs
- future AI content engine
- API documentation via Scramble
- audit logging via Spatie Activitylog

It does not include:

- Livewire tasks
- Blade tasks
- admin UI tasks
- public frontend tasks

## Service Scope Principles

- API and service logic only
- thin controllers, validated requests, DTOs, services, repositories, resources
- task size should remain agent-friendly
- public read safety must respect published content boundaries
- AI outputs must never publish directly

## Stack

- Laravel 13
- PHP 8.4
- MySQL
- Redis
- Laravel Queue
- Laravel Scheduler
- Scramble
- Spatie Activitylog
- optional Spatie Permission if API roles are needed
- Cloudflare R2 integration
- Laravel Pint
- Larastan
- Pest

## Story Point Scale

- `1`
- `2`
- `3`
- `5`
- `8`
- `13`
- `21`

## Phase Summary

- Phase 0 — Service Foundation
- Phase 1 — Core APIs
- Phase 2 — Publishing & SEO Services
- Phase 3 — AI Content Engine
- Phase 4 — Advanced Services

---

## WB-SVC-001 — Initialize Laravel 13 service application

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `2`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Initialize the Laravel 13 codebase for the service repository and confirm the PHP 8.4 runtime baseline.

### Deliverables

- Laravel application skeleton
- runtime baseline verified

### Dependencies

- none

### Acceptance Criteria

- `php artisan about` runs successfully
- service app boots without fatal errors

### Suggested Files / Areas

- `composer.json`
- `artisan`
- `bootstrap/`
- `app/`

### Validation

- `php artisan --version`
- `php artisan about`

### Notes

- keep this repo service-only; no UI scaffolding

---

## WB-SVC-002 — Configure Docker and local service environment

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Set up Docker and local development for the Laravel service runtime, MySQL, and Redis.

### Deliverables

- Docker config
- local startup flow
- containerized app, MySQL, Redis

### Dependencies

- `WB-SVC-001`

### Acceptance Criteria

- containers start successfully
- app can connect to MySQL and Redis locally

### Suggested Files / Areas

- `docker-compose.yml`
- `Dockerfile`
- `.env.example`
- `README.md`

### Validation

- `docker compose up -d`
- `docker compose ps`
- `php artisan about`

### Notes

- keep services minimal and repo-specific

---

## WB-SVC-003 — Configure environment variables and application config

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Configure environment keys and config wiring for database, cache, queue, storage, app URL, and logging.

### Deliverables

- updated `.env.example`
- config defaults reviewed

### Dependencies

- `WB-SVC-001`
- `WB-SVC-002`

### Acceptance Criteria

- required keys are present in `.env.example`
- app boots with documented configuration values

### Suggested Files / Areas

- `.env.example`
- `config/*.php`

### Validation

- `php artisan config:clear`
- `php artisan about`

### Notes

- no UI-only environment variables

---

## WB-SVC-004 — Configure MySQL, Redis, queues, scheduler, and storage disks

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Wire the infrastructure features the service layer depends on.

### Deliverables

- MySQL connection
- Redis cache and queue config
- scheduler baseline
- storage disk scaffolding

### Dependencies

- `WB-SVC-002`
- `WB-SVC-003`

### Acceptance Criteria

- migrations can run against MySQL
- queue worker can run once
- scheduler can list commands

### Suggested Files / Areas

- `config/database.php`
- `config/queue.php`
- `config/cache.php`
- `routes/console.php`

### Validation

- `php artisan migrate:status`
- `php artisan queue:work --once`
- `php artisan schedule:list`

### Notes

- scheduler should support future SEO and AI jobs

---

## WB-SVC-005 — Configure Cloudflare R2 disk integration

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Add and configure the S3-compatible storage integration for Cloudflare R2.

### Deliverables

- Flysystem S3 driver
- R2 disk config
- environment-driven credentials

### Dependencies

- `WB-SVC-003`
- `WB-SVC-004`

### Acceptance Criteria

- service can write, read, and delete files on the R2 disk

### Suggested Files / Areas

- `composer.json`
- `config/filesystems.php`
- `.env.example`

### Validation

- `php artisan tinker` storage smoke test

### Notes

- this is required for media and future AI image flows

---

## WB-SVC-006 — Install and configure Scramble for API documentation

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Install Scramble and configure it as the API documentation generator for the service repository.

### Deliverables

- Scramble installed
- base config
- docs route or generation workflow

### Dependencies

- `WB-SVC-001`

### Acceptance Criteria

- API docs can be generated or browsed locally
- service endpoints can be documented through code annotations or route reflection

### Suggested Files / Areas

- `composer.json`
- `config/scramble.php`
- routes

### Validation

- Scramble docs generation or route smoke test

### Notes

- keep docs generation aligned with service-only routes

---

## WB-SVC-007 — Install and configure Spatie Activitylog

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Install audit logging and configure base activity log behavior for critical service-side content operations.

### Deliverables

- Activitylog installed
- config published
- baseline activity model wiring

### Dependencies

- `WB-SVC-001`

### Acceptance Criteria

- activity log migration exists
- service can record audit events for a sample model

### Suggested Files / Areas

- `composer.json`
- `config/activitylog.php`
- migrations
- models

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- prioritize auditable editorial state changes

---

## WB-SVC-008 — Install Spatie Permission if API roles are required

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `3`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Install role and permission support if policy needs exceed a simple enum-based admin-only model.

### Deliverables

- package installation decision
- implementation if needed

### Dependencies

- `WB-SVC-001`

### Acceptance Criteria

- package is either installed and configured or explicitly deferred with documented reasoning

### Suggested Files / Areas

- `composer.json`
- auth config
- permissions migrations

### Validation

- `php artisan test`

### Notes

- defer if enum roles are sufficient for MVP

---

## WB-SVC-009 — Install Pint, Larastan, and Pest

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Set up formatting, static analysis, and testing tools for the service repository.

### Deliverables

- Pint
- Larastan
- Pest

### Dependencies

- `WB-SVC-001`

### Acceptance Criteria

- formatting, static analysis, and test commands run locally

### Suggested Files / Areas

- `composer.json`
- `phpstan.neon`
- `pint.json`
- `tests/`

### Validation

- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

### Notes

- keep configs service-repo specific

---

## WB-SVC-010 — Set up API response format and exception handling

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Define consistent JSON response envelopes and structured exception-to-error mapping.

### Deliverables

- success response conventions
- error response conventions
- centralized exception rendering

### Dependencies

- `WB-SVC-001`

### Acceptance Criteria

- validation, auth, and not-found errors return consistent JSON shapes
- internal errors do not leak sensitive details

### Suggested Files / Areas

- `app/Exceptions/`
- `app/Http/Resources/`
- bootstrap exception handling

### Validation

- `php artisan test`
- request-level feature tests

### Notes

- align with `docs/OPENAPI_SPEC.md`

---

## WB-SVC-011 — Establish controller/request/DTO/service/repository/resource conventions

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create the baseline service-layer conventions and base abstractions for backend features.

### Deliverables

- directory structure
- base DTO conventions
- repository conventions
- API resource conventions

### Dependencies

- `WB-SVC-001`

### Acceptance Criteria

- new service features can follow a documented layered pattern
- at least one sample flow proves the structure is usable

### Suggested Files / Areas

- `app/Modules/`
- `app/Http/Requests/`
- `app/Http/Resources/`
- `app/Support/`

### Validation

- manual architecture review
- `php artisan test`

### Notes

- must match service-only architecture guidance

---

## WB-SVC-012 — Configure CI validation for the service repository

**Phase:** Phase 0 — Service Foundation  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create CI automation for tests, formatting, and static analysis.

### Deliverables

- CI workflow
- app bootstrap for CI
- quality gates

### Dependencies

- `WB-SVC-009`

### Acceptance Criteria

- CI runs Pint, Larastan, and Pest
- failing checks block green status

### Suggested Files / Areas

- `.github/workflows/`
- `composer.json`

### Validation

- CI run
- local workflow smoke test if possible

### Notes

- no frontend build steps required

---

## WB-SVC-013 — Implement auth and admin API support foundation

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Add backend auth support for admin consumers, policy enforcement, and authenticated API entry points.

### Deliverables

- auth endpoint support
- admin route protection
- user role checks or permissions

### Dependencies

- `WB-SVC-006`
- `WB-SVC-010`
- `WB-SVC-011`

### Acceptance Criteria

- protected service endpoints require auth
- unauthorized requests receive consistent JSON errors

### Suggested Files / Areas

- `routes/api.php`
- auth controllers
- policies
- user model

### Validation

- `php artisan test`
- auth feature tests

### Notes

- support admin/API consumers, not admin UI screens

---

## WB-SVC-014 — Implement category schema, model, and repository layer

**Phase:** Phase 1 — Core APIs  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create the persistence layer for categories.

### Deliverables

- migration
- model
- repository

### Dependencies

- `WB-SVC-004`
- `WB-SVC-011`

### Acceptance Criteria

- categories table matches design
- repository supports core reads and writes

### Suggested Files / Areas

- migrations
- `app/Models/Category.php`
- `app/Modules/Categories/Repositories/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- include slug and active state support

---

## WB-SVC-015 — Implement categories API endpoints and resources

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Expose admin and public-safe categories endpoints.

### Deliverables

- controllers
- requests
- resources
- route definitions

### Dependencies

- `WB-SVC-013`
- `WB-SVC-014`

### Acceptance Criteria

- admin CRUD endpoints function
- public list endpoints expose only active categories

### Suggested Files / Areas

- `routes/api.php`
- `app/Modules/Categories/Http/`
- `app/Http/Resources/`

### Validation

- `php artisan test`
- API feature tests

### Notes

- ensure Scramble can document endpoints cleanly

---

## WB-SVC-016 — Implement tag schema, model, and repository layer

**Phase:** Phase 1 — Core APIs  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create persistence support for tags and post tag assignments.

### Deliverables

- `tags` migration
- `post_tags` migration
- tag model
- repository

### Dependencies

- `WB-SVC-004`
- `WB-SVC-011`

### Acceptance Criteria

- tags and pivot tables migrate successfully
- repository supports CRUD and lookups

### Suggested Files / Areas

- migrations
- `app/Models/Tag.php`
- `app/Modules/Tags/Repositories/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- tag logic stays service-side only

---

## WB-SVC-017 — Implement tags API endpoints and resources

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Expose backend tag management endpoints.

### Deliverables

- tag controllers
- requests
- resources

### Dependencies

- `WB-SVC-013`
- `WB-SVC-016`

### Acceptance Criteria

- tags can be created, updated, listed, and deleted through API

### Suggested Files / Areas

- `app/Modules/Tags/Http/`
- `routes/api.php`

### Validation

- `php artisan test`

### Notes

- public tag APIs can remain deferred unless truly needed

---

## WB-SVC-018 — Implement media schema, model, and service abstractions

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create media persistence and the core service abstractions around file storage.

### Deliverables

- media migration
- media model
- media service contracts

### Dependencies

- `WB-SVC-005`
- `WB-SVC-011`

### Acceptance Criteria

- media metadata persists in MySQL
- service abstractions exist for upload/read/delete

### Suggested Files / Areas

- migrations
- `app/Models/Media.php`
- `app/Modules/Media/Services/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- align with `docs/MEDIA_SERVICE.md`

---

## WB-SVC-019 — Implement media upload, batch upload, and metadata APIs

**Phase:** Phase 1 — Core APIs  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Build backend endpoints for single and multiple uploads plus metadata editing.

### Deliverables

- upload endpoint
- batch upload endpoint
- metadata update endpoint

### Dependencies

- `WB-SVC-013`
- `WB-SVC-018`

### Acceptance Criteria

- files can be uploaded to R2 through API
- metadata persists and is editable

### Suggested Files / Areas

- `app/Modules/Media/Http/`
- storage services
- requests and resources

### Validation

- `php artisan test`
- storage integration tests

### Notes

- no media library UI in this repo backlog

---

## WB-SVC-020 — Implement media search, delete, and usage tracking APIs

**Phase:** Phase 1 — Core APIs  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Expose media retrieval and safe deletion behavior, including usage awareness.

### Deliverables

- media listing endpoint
- filtering and search
- delete endpoint
- usage lookup support

### Dependencies

- `WB-SVC-019`

### Acceptance Criteria

- API can list and filter media
- delete is blocked when usage rules forbid it

### Suggested Files / Areas

- media controllers
- query services
- usage services

### Validation

- `php artisan test`

### Notes

- usage logic must account for featured media and SEO images

---

## WB-SVC-021 — Implement template schema, models, and repository layer

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create persistence support for templates and template blocks.

### Deliverables

- template migrations
- models
- repositories

### Dependencies

- `WB-SVC-004`
- `WB-SVC-011`

### Acceptance Criteria

- templates and template blocks persist correctly
- repositories support ordered block retrieval

### Suggested Files / Areas

- migrations
- `app/Models/Template.php`
- `app/Models/TemplateBlock.php`
- template repositories

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- no visual designer scope

---

## WB-SVC-022 — Implement template APIs and preview payload generation

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Expose template CRUD APIs plus preview and post-seeding payload support.

### Deliverables

- template CRUD endpoints
- preview endpoint
- seed-post payload endpoint

### Dependencies

- `WB-SVC-013`
- `WB-SVC-021`

### Acceptance Criteria

- templates can be managed via API
- preview payload reflects template blocks and config

### Suggested Files / Areas

- template controllers
- resources
- services

### Validation

- `php artisan test`

### Notes

- align with `docs/TEMPLATE_ENGINE.md`

---

## WB-SVC-023 — Implement post schema, model, and repository layer

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create post persistence with relationships and publish-state fields.

### Deliverables

- posts migration
- post model
- repositories

### Dependencies

- `WB-SVC-014`
- `WB-SVC-016`
- `WB-SVC-018`
- `WB-SVC-021`

### Acceptance Criteria

- posts schema matches design
- repositories support admin and public-safe query patterns

### Suggested Files / Areas

- migrations
- `app/Models/Post.php`
- `app/Modules/Posts/Repositories/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- include relationships to category, tags, template, media

---

## WB-SVC-024 — Implement post block schema and block-type support

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create post block persistence and allowed block type definitions.

### Deliverables

- post blocks migration
- model
- block type enum or value object

### Dependencies

- `WB-SVC-021`
- `WB-SVC-023`

### Acceptance Criteria

- supported block types are persisted with deterministic ordering

### Suggested Files / Areas

- migrations
- `app/Models/PostBlock.php`
- `app/Enums/` or support classes

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- support heading, paragraph, image, quote, list, code, FAQ, callout

---

## WB-SVC-025 — Implement post command services and DTOs

**Phase:** Phase 1 — Core APIs  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Build service-layer use cases for creating, updating, and deleting posts and blocks.

### Deliverables

- create/update/delete services
- DTOs
- transaction handling

### Dependencies

- `WB-SVC-011`
- `WB-SVC-023`
- `WB-SVC-024`

### Acceptance Criteria

- post mutations happen through services, not controllers
- block payload validation and persistence are consistent

### Suggested Files / Areas

- `app/Modules/Posts/DTOs/`
- `app/Modules/Posts/Services/`

### Validation

- `php artisan test`

### Notes

- prepare for template seeding and AI draft generation later

---

## WB-SVC-026 — Implement posts API endpoints and resources

**Phase:** Phase 1 — Core APIs  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Expose CRUD APIs for posts and structured block payloads.

### Deliverables

- controllers
- requests
- resources
- filtering and sorting support

### Dependencies

- `WB-SVC-013`
- `WB-SVC-025`

### Acceptance Criteria

- admin API supports create, list, view, update, delete
- public-safe list and detail patterns are possible later without rewriting domain logic

### Suggested Files / Areas

- post controllers
- requests
- resources
- routes

### Validation

- `php artisan test`

### Notes

- match `docs/OPENAPI_SPEC.md`

---

## WB-SVC-027 — Implement publish, schedule, and unpublish services and endpoints

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create explicit state transition services and API endpoints for publishing workflows.

### Deliverables

- publish service
- schedule service
- unpublish service
- endpoints

### Dependencies

- `WB-SVC-025`
- `WB-SVC-026`

### Acceptance Criteria

- invalid state transitions are blocked
- scheduled state stores future publish intent

### Suggested Files / Areas

- post services
- controllers
- requests

### Validation

- `php artisan test`

### Notes

- published state drives public SEO-safe data exposure

---

## WB-SVC-028 — Implement knowledge base schema, model, and repository layer

**Phase:** Phase 1 — Core APIs  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create persistence support for knowledge base entries.

### Deliverables

- migration
- model
- repository

### Dependencies

- `WB-SVC-004`
- `WB-SVC-011`

### Acceptance Criteria

- knowledge base entries persist with type and status

### Suggested Files / Areas

- migrations
- `app/Models/KnowledgeBaseEntry.php`
- repositories

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- align with `docs/KNOWLEDGE_BASE.md`

---

## WB-SVC-029 — Implement knowledge base APIs including search and linking primitives

**Phase:** Phase 1 — Core APIs  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Expose CRUD, search, filtering, and future-safe linking endpoints for knowledge entries.

### Deliverables

- CRUD endpoints
- search/filter support
- post/topic linking endpoints or service hooks

### Dependencies

- `WB-SVC-013`
- `WB-SVC-028`

### Acceptance Criteria

- entries can be created, updated, archived, searched, and filtered via API

### Suggested Files / Areas

- knowledge base controllers
- query services
- routes

### Validation

- `php artisan test`

### Notes

- relationships may evolve, but API shape should anticipate them

---

## WB-SVC-030 — Implement SEO metadata schema, models, and repository layer

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create persistence and data access support for SEO metadata across content entities.

### Deliverables

- migration
- model
- repository

### Dependencies

- `WB-SVC-014`
- `WB-SVC-023`
- `WB-SVC-028`

### Acceptance Criteria

- SEO metadata can be stored for posts and categories
- polymorphic one-to-one logic works

### Suggested Files / Areas

- migrations
- `app/Models/SeoMetadata.php`
- SEO repositories

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- keep schema extensible for future entities

---

## WB-SVC-031 — Implement SEO metadata APIs and resource serializers

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Expose backend endpoints for managing SEO metadata.

### Deliverables

- SEO controllers
- requests
- resources

### Dependencies

- `WB-SVC-013`
- `WB-SVC-030`

### Acceptance Criteria

- SEO metadata is readable and writable through API
- canonical, robots, OG, and schema-type fields are supported

### Suggested Files / Areas

- SEO controllers
- requests
- resources

### Validation

- `php artisan test`

### Notes

- scoring endpoint can come later in Phase 2

---

## WB-SVC-032 — Add Activitylog coverage for content-changing services

**Phase:** Phase 1 — Core APIs  
**Story Points:** `5`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Instrument key mutations so post, category, media, template, and SEO changes are auditable.

### Deliverables

- activity events on mutations
- audit payload conventions

### Dependencies

- `WB-SVC-007`
- `WB-SVC-025`
- `WB-SVC-029`
- `WB-SVC-031`

### Acceptance Criteria

- important content mutations produce auditable entries

### Suggested Files / Areas

- models
- services
- activity config

### Validation

- `php artisan test`
- activity assertions in feature tests

### Notes

- prioritize editorially sensitive operations

---

## WB-SVC-033 — Generate Scramble docs coverage for core APIs

**Phase:** Phase 1 — Core APIs  
**Story Points:** `3`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Ensure core service endpoints are discoverable and clearly documented through Scramble.

### Deliverables

- route docs coverage
- schema inference validation
- examples where needed

### Dependencies

- `WB-SVC-015`
- `WB-SVC-017`
- `WB-SVC-019`
- `WB-SVC-022`
- `WB-SVC-026`
- `WB-SVC-029`
- `WB-SVC-031`

### Acceptance Criteria

- core endpoints appear correctly in generated docs

### Suggested Files / Areas

- routes
- request classes
- resources

### Validation

- Scramble generation or docs route review

### Notes

- docs should stay in sync with implementation patterns

---

## WB-SVC-034 — Implement slug generation service

**Phase:** Phase 2 — Publishing & SEO Services  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Centralize slug generation and uniqueness behavior for posts, categories, tags, templates, and knowledge entries.

### Deliverables

- slug service
- unique suffix strategy

### Dependencies

- `WB-SVC-014`
- `WB-SVC-016`
- `WB-SVC-021`
- `WB-SVC-023`
- `WB-SVC-028`

### Acceptance Criteria

- slugs are deterministic and unique

### Suggested Files / Areas

- support services
- model hooks or service-layer usage

### Validation

- `php artisan test`

### Notes

- prefer service-level control over magic-only behavior

---

## WB-SVC-035 — Implement canonical URL generation service

**Phase:** Phase 2 — Publishing & SEO Services  
**Story Points:** `3`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create a service that derives or respects canonical URLs for supported content types.

### Deliverables

- canonical generation service
- content-type aware URL derivation

### Dependencies

- `WB-SVC-031`
- `WB-SVC-034`

### Acceptance Criteria

- canonical values are available for published content payloads

### Suggested Files / Areas

- `app/Modules/Seo/Services/`

### Validation

- `php artisan test`

### Notes

- should support override and default behavior

---

## WB-SVC-036 — Implement sitemap data API

**Phase:** Phase 2 — Publishing & SEO Services  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Expose a service-level API or command-oriented data provider for sitemap generation from published content.

### Deliverables

- sitemap query service
- sitemap endpoint or command payload

### Dependencies

- `WB-SVC-026`
- `WB-SVC-027`
- `WB-SVC-035`

### Acceptance Criteria

- sitemap data includes published content only

### Suggested Files / Areas

- SEO services
- console commands
- routes if endpoint-based

### Validation

- `php artisan test`

### Notes

- can be consumed by internal generators rather than public UI

---

## WB-SVC-037 — Implement RSS data API

**Phase:** Phase 2 — Publishing & SEO Services  
**Story Points:** `3`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Expose feed-ready published content data for RSS generation.

### Deliverables

- RSS query service
- feed serialization support

### Dependencies

- `WB-SVC-026`
- `WB-SVC-027`

### Acceptance Criteria

- latest published articles can be returned in feed-friendly order

### Suggested Files / Areas

- feed services
- resources

### Validation

- `php artisan test`

### Notes

- keep separate from frontend rendering

---

## WB-SVC-038 — Implement schema data API

**Phase:** Phase 2 — Publishing & SEO Services  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create service-side schema payload generation for articles, breadcrumbs, organization, website, and FAQ content.

### Deliverables

- schema builders
- payload serializers

### Dependencies

- `WB-SVC-030`
- `WB-SVC-031`
- `WB-SVC-026`

### Acceptance Criteria

- schema payloads can be generated from service data without UI logic

### Suggested Files / Areas

- `app/Modules/Seo/Schema/`

### Validation

- `php artisan test`

### Notes

- aligns with SEO strategy and implementation specs

---

## WB-SVC-039 — Implement internal linking foundation services

**Phase:** Phase 2 — Publishing & SEO Services  
**Story Points:** `5`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Create the first backend services for content relationship discovery and internal link suggestion inputs.

### Deliverables

- related-content query service
- internal link suggestion service baseline

### Dependencies

- `WB-SVC-026`
- `WB-SVC-029`

### Acceptance Criteria

- service can return candidate related content for a post or draft context

### Suggested Files / Areas

- SEO/internal-linking services
- query services

### Validation

- `php artisan test`

### Notes

- no UI recommendation panel in this repo backlog

---

## WB-SVC-040 — Implement SEO scoring foundation service

**Phase:** Phase 2 — Publishing & SEO Services  
**Story Points:** `5`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Create a backend scoring service that evaluates content quality signals for editorial use.

### Deliverables

- scoring service
- scoring breakdown structure
- endpoint or internal API support

### Dependencies

- `WB-SVC-031`
- `WB-SVC-038`
- `WB-SVC-039`

### Acceptance Criteria

- a post can receive a structured SEO score with subscores

### Suggested Files / Areas

- `app/Modules/Seo/Scoring/`
- controllers or internal services

### Validation

- `php artisan test`

### Notes

- treat as advisory, not publish gate by default

---

## WB-SVC-041 — Implement AI provider abstraction and core orchestration contracts

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create the provider-agnostic contracts and orchestration entry points for AI operations.

### Deliverables

- provider contracts
- normalized result objects
- orchestration service interfaces

### Dependencies

- `WB-SVC-004`
- `WB-SVC-011`

### Acceptance Criteria

- domain services can call AI through abstractions, not vendor-specific classes

### Suggested Files / Areas

- `app/Infrastructure/Ai/`
- `app/Modules/Ai/Services/`

### Validation

- `php artisan test`

### Notes

- must support OpenAI, Anthropic, Gemini

---

## WB-SVC-042 — Implement prompt management schema and APIs

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create storage and service APIs for prompt templates, categories, and versions.

### Deliverables

- prompt template schema
- prompt APIs
- versioning support

### Dependencies

- `WB-SVC-041`

### Acceptance Criteria

- prompts can be stored, categorized, versioned, and retrieved through API

### Suggested Files / Areas

- migrations
- prompt models
- controllers
- services

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- prompts should not be code-only constants

---

## WB-SVC-043 — Implement topic schema, repository, and APIs

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create topic persistence and APIs for topic queue behavior.

### Deliverables

- `topics` migration
- topic model and repository
- CRUD and status transition endpoints

### Dependencies

- `WB-SVC-011`
- `WB-SVC-042`

### Acceptance Criteria

- topics support suggested, approved, rejected, and used states

### Suggested Files / Areas

- migrations
- `app/Models/Topic.php`
- topic controllers

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- topic queue UI is out of scope; API only

---

## WB-SVC-044 — Implement topic discovery services and scheduled job

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create the AI-assisted topic suggestion workflow and a schedulable discovery job.

### Deliverables

- topic discovery service
- scheduled job
- category mapping baseline

### Dependencies

- `WB-SVC-041`
- `WB-SVC-042`
- `WB-SVC-043`

### Acceptance Criteria

- discovery creates topic suggestions only
- scheduled discovery can run through queue and scheduler

### Suggested Files / Areas

- AI discovery services
- jobs
- scheduler config

### Validation

- `php artisan test`
- `php artisan schedule:list`

### Notes

- no auto-approval behavior

---

## WB-SVC-045 — Implement content blueprint service

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create the service that turns approved topics into structured article blueprints.

### Deliverables

- blueprint DTOs
- blueprint generation service
- template mapping support

### Dependencies

- `WB-SVC-022`
- `WB-SVC-029`
- `WB-SVC-043`

### Acceptance Criteria

- approved topics can generate structured blueprints using templates and knowledge context

### Suggested Files / Areas

- AI blueprint services
- DTOs
- tests

### Validation

- `php artisan test`

### Notes

- blueprint output should map to content blocks, not HTML

---

## WB-SVC-046 — Implement draft generation service and APIs

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `13`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create the AI-assisted draft generation workflow that produces draft posts and post blocks.

### Deliverables

- generation service
- draft persistence support
- generation endpoint

### Dependencies

- `WB-SVC-025`
- `WB-SVC-041`
- `WB-SVC-045`

### Acceptance Criteria

- generated output is stored only as draft content
- no publish state bypass is possible

### Suggested Files / Areas

- AI generation services
- post services
- controllers
- jobs

### Validation

- `php artisan test`

### Notes

- raw generated HTML must not be primary source content

---

## WB-SVC-047 — Implement SEO and FAQ generation services

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Add AI-assisted generation of metadata and structured FAQ suggestions.

### Deliverables

- SEO suggestion service
- FAQ suggestion service
- optional tag suggestion support

### Dependencies

- `WB-SVC-030`
- `WB-SVC-038`
- `WB-SVC-046`

### Acceptance Criteria

- metadata and FAQ suggestions are stored as editable suggestions, not silently applied output

### Suggested Files / Areas

- AI generation services
- SEO services
- block services

### Validation

- `php artisan test`

### Notes

- use structured FAQ payloads

---

## WB-SVC-048 — Implement AI job tracking schema and APIs

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `8`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Create persistent AI job tracking for status, retries, inputs, and outputs.

### Deliverables

- `ai_jobs` migration
- model and repository
- read/retry APIs

### Dependencies

- `WB-SVC-041`

### Acceptance Criteria

- AI jobs persist state transitions and are queryable via API

### Suggested Files / Areas

- migrations
- models
- controllers
- repositories

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- align with `docs/AI_CONTENT_ENGINE.md`

---

## WB-SVC-049 — Implement token and cost tracking schema and services

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Capture AI usage metrics and cost data per call and per job.

### Deliverables

- `ai_job_costs` migration
- usage recording service
- reporting query support

### Dependencies

- `WB-SVC-048`

### Acceptance Criteria

- token and cost data can be persisted and queried by provider, model, or job

### Suggested Files / Areas

- migrations
- models
- usage services

### Validation

- `php artisan migrate`
- `php artisan test`

### Notes

- support estimated and actual cost fields

---

## WB-SVC-050 — Implement scheduled AI orchestration and retry-safe queue flow

**Phase:** Phase 3 — AI Content Engine  
**Story Points:** `5`  
**Priority:** Must Have  
**Status:** Backlog

### Description

Wire AI workflows into queue and scheduler infrastructure with retry-safe handling.

### Deliverables

- scheduled jobs
- queue dispatch policy
- retry safeguards

### Dependencies

- `WB-SVC-044`
- `WB-SVC-046`
- `WB-SVC-048`

### Acceptance Criteria

- AI workflows can run asynchronously
- retries do not duplicate draft creation or corrupt state

### Suggested Files / Areas

- jobs
- scheduler
- AI orchestration services

### Validation

- `php artisan schedule:list`
- `php artisan test`

### Notes

- keep queue names explicit, especially `ai`

---

## WB-SVC-051 — Implement AI image generation service

**Phase:** Phase 4 — Advanced Services  
**Story Points:** `8`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Create backend support for AI-generated image workflows and media persistence.

### Deliverables

- image generation service
- media persistence integration
- AI job linkage

### Dependencies

- `WB-SVC-018`
- `WB-SVC-041`
- `WB-SVC-048`

### Acceptance Criteria

- generated images are persisted as media records with `ai_generated` source type

### Suggested Files / Areas

- AI image services
- media services
- jobs

### Validation

- `php artisan test`

### Notes

- no UI selection flow in this backlog

---

## WB-SVC-052 — Implement stock image provider abstraction and attribution support

**Phase:** Phase 4 — Advanced Services  
**Story Points:** `8`  
**Priority:** Could Have  
**Status:** Backlog

### Description

Create backend support for stock image provider integration and attribution data handling.

### Deliverables

- provider abstraction
- attribution persistence rules
- import service

### Dependencies

- `WB-SVC-018`
- `WB-SVC-020`

### Acceptance Criteria

- stock assets can be imported into the media service with source and attribution metadata

### Suggested Files / Areas

- media stock services
- migrations if additional fields are needed

### Validation

- `php artisan test`

### Notes

- keep provider-specific logic isolated

---

## WB-SVC-053 — Implement analytics data ingestion and reporting APIs

**Phase:** Phase 4 — Advanced Services  
**Story Points:** `8`  
**Priority:** Could Have  
**Status:** Backlog

### Description

Create backend APIs for analytics data ingestion or reporting around content and author performance.

### Deliverables

- analytics data model
- reporting endpoints
- query services

### Dependencies

- `WB-SVC-026`

### Acceptance Criteria

- content performance metrics can be stored or exposed through API

### Suggested Files / Areas

- analytics models
- controllers
- query services

### Validation

- `php artisan test`

### Notes

- reporting UI is out of scope here

---

## WB-SVC-054 — Implement SEO recommendation APIs

**Phase:** Phase 4 — Advanced Services  
**Story Points:** `8`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Expose recommendation-oriented APIs for SEO improvements, missing metadata, and score breakdowns.

### Deliverables

- recommendation service
- recommendation endpoints
- structured recommendation payloads

### Dependencies

- `WB-SVC-039`
- `WB-SVC-040`
- `WB-SVC-047`

### Acceptance Criteria

- a post can return actionable SEO recommendations via API

### Suggested Files / Areas

- SEO recommendation services
- controllers
- resources

### Validation

- `php artisan test`

### Notes

- recommendations should be explainable, not opaque

---

## WB-SVC-055 — Implement content refresh recommendation APIs

**Phase:** Phase 4 — Advanced Services  
**Story Points:** `8`  
**Priority:** Should Have  
**Status:** Backlog

### Description

Create backend services that identify stale content and expose refresh-oriented recommendations.

### Deliverables

- stale content detection service
- refresh recommendation endpoint

### Dependencies

- `WB-SVC-040`
- `WB-SVC-053`
- `WB-SVC-054`

### Acceptance Criteria

- backend can identify content likely needing refresh based on age, metadata, or performance inputs

### Suggested Files / Areas

- content refresh services
- query services
- controllers

### Validation

- `php artisan test`

### Notes

- keep recommendation logic additive and inspectable

---

## Recommended Milestone Cuts

### Service Milestone A — Foundation

- `WB-SVC-001` to `WB-SVC-012`

### Service Milestone B — Core Domain APIs

- `WB-SVC-013` to `WB-SVC-033`

### Service Milestone C — Publishing And SEO Services

- `WB-SVC-034` to `WB-SVC-040`

### Service Milestone D — AI Engine Foundations

- `WB-SVC-041` to `WB-SVC-050`

### Service Milestone E — Advanced Services

- `WB-SVC-051` to `WB-SVC-055`

## Summary

This backlog keeps the `service` repository focused on backend responsibilities only: APIs, business logic, persistence, storage integration, documentation, audit logging, SEO services, and future AI orchestration. It deliberately excludes Livewire, Blade, admin screens, and public frontend work while preserving a phased path from service foundation through advanced backend intelligence services.
