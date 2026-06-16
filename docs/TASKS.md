# Tasks: Wide Web Blog

## Document Purpose

This document defines the implementation backlog for the Wide Web Blog platform as a single Laravel application. It translates the product vision, MVP scope, architecture, database design, content strategy, SEO strategy, and roadmap into coding-agent-executable tasks.

This backlog covers:

- Laravel foundation
- admin CMS
- public blog website
- media service
- SEO features
- knowledge base
- AI publishing
- advanced publishing capabilities

It does not split work into separate backend, admin, or frontend applications. Everything belongs to the same Laravel codebase.

## Execution Principles

- tasks must be small and focused
- dependencies must be explicit
- acceptance criteria must be concrete
- validation must be runnable by coding agents
- backlog order should favor incremental delivery
- Phase 1 must optimize for launch speed and SEO quality
- AI features must never bypass human approval

## Technology Baseline

- Laravel 13
- PHP 8.4
- Livewire
- MySQL
- Redis
- Laravel Queue
- Laravel Scheduler
- Cloudflare R2
- Docker
- Laravel Pint
- Larastan
- Pest

## Story Point Scale

Use Fibonacci story points:

- `1`
- `2`
- `3`
- `5`
- `8`
- `13`
- `21`

## Package Plan

The following package plan should be treated as the starting point for implementation.

| Package / Tool | Why It Is Needed | Requirement | Installation Task | Configuration Task |
|---|---|---|---|---|
| `livewire/livewire` | admin CMS and interactive publishing UI | MVP | `WB-005` | `WB-005` |
| `laravel/breeze` with Livewire stack | fast, maintainable auth bootstrap | MVP | `WB-006` | `WB-006` |
| `league/flysystem-aws-s3-v3` | Cloudflare R2-compatible storage driver | MVP | `WB-007` | `WB-007` |
| `cviebrock/eloquent-sluggable` or equivalent | reliable slug generation and overrides | MVP | `WB-008` | `WB-008` |
| `spatie/laravel-sitemap` or equivalent | sitemap generation from publish state | MVP | `WB-008` | `WB-043` |
| Custom SEO module preferred over generic SEO package | tighter control over metadata, schema, canonicals | MVP | `WB-037` | `WB-038` |
| `pestphp/pest` | testing framework | MVP | `WB-009` | `WB-009` |
| `larastan/larastan` | static analysis and type quality | MVP | `WB-009` | `WB-009` |
| `laravel/pint` | code style enforcement | MVP | `WB-009` | `WB-009` |
| `nunomaduro/collision` | local debugging and CLI feedback | MVP | `WB-009` | `WB-009` |
| Laravel Scout optional, defer by default | search abstraction if MySQL full-text becomes limiting | Future | `WB-041` if adopted | `WB-041` if adopted |

## Phase Summary

- Phase 0: Foundation & Agent Environment
- Phase 1: MVP Blog Platform
- Phase 2: AI-Assisted Publishing
- Phase 3: Advanced Publishing

---

## WB-001

### Title

Initialize Laravel 13 application

### Phase

Phase 0

### Description

Create the base Laravel application structure for the single-codebase platform and verify the runtime baseline.

### Deliverables

- Laravel 13 project initialized
- PHP 8.4 compatibility confirmed
- baseline app bootstraps locally

### Dependencies

- none

### Acceptance Criteria

- `php artisan about` runs successfully
- base app boots without fatal errors
- repository contains standard Laravel app structure

### Suggested Files / Areas

- `composer.json`
- `artisan`
- `bootstrap/`
- `app/`

### Validation

- `php artisan --version`
- `php artisan about`

### Story Points

`2`

---

## WB-002

### Title

Configure Docker development environment

### Phase

Phase 0

### Description

Create a reproducible local development environment for app runtime, MySQL, Redis, and supporting services.

### Deliverables

- Docker configuration
- app service
- MySQL service
- Redis service
- local startup instructions

### Dependencies

- `WB-001`

### Acceptance Criteria

- app containers start successfully
- Laravel app can connect to MySQL and Redis in Docker
- basic local workflow is documented

### Suggested Files / Areas

- `docker-compose.yml` or equivalent
- `Dockerfile`
- `.env.example`
- `README.md`

### Validation

- `docker compose up -d`
- `docker compose ps`
- `php artisan about`

### Story Points

`5`

---

## WB-003

### Title

Configure environment variables and application configuration

### Phase

Phase 0

### Description

Define environment variable conventions and Laravel config wiring for local, test, and production-ready operation.

### Deliverables

- `.env.example` updated
- app config defaults reviewed
- queue, cache, mail, app URL, and storage config wired

### Dependencies

- `WB-001`
- `WB-002`

### Acceptance Criteria

- required environment keys are present in `.env.example`
- app boots using documented config values
- no hardcoded local-only secrets or paths remain

### Suggested Files / Areas

- `.env.example`
- `config/*.php`

### Validation

- `php artisan config:clear`
- `php artisan about`

### Story Points

`3`

---

## WB-004

### Title

Configure MySQL, Redis, queues, scheduler, and storage foundations

### Phase

Phase 0

### Description

Wire the core infrastructure features the application depends on before domain work begins.

### Deliverables

- MySQL connection configured
- Redis configured for cache and queue
- queue driver configured
- scheduler enabled in runtime assumptions
- storage disk scaffolding configured

### Dependencies

- `WB-002`
- `WB-003`

### Acceptance Criteria

- app connects to MySQL
- queue connection is configured and testable
- cache uses Redis
- scheduler can be listed with at least a placeholder command

### Suggested Files / Areas

- `config/database.php`
- `config/queue.php`
- `config/cache.php`
- `routes/console.php`
- `app/Console/Kernel.php` if applicable

### Validation

- `php artisan migrate:status`
- `php artisan queue:work --once`
- `php artisan schedule:list`

### Story Points

`5`

---

## WB-005

### Title

Install and configure Livewire

### Phase

Phase 0

### Description

Add Livewire as the foundation for interactive admin and selected public UI.

### Deliverables

- Livewire installed
- Livewire assets and config integrated
- base component workflow verified

### Dependencies

- `WB-001`

### Acceptance Criteria

- a sample Livewire component renders correctly
- Livewire is available for admin and frontend usage

### Suggested Files / Areas

- `composer.json`
- `config/livewire.php`
- `app/Livewire/`
- `resources/views/`

### Validation

- `php artisan livewire:make TestComponent`
- manual browser validation

### Story Points

`2`

---

## WB-006

### Title

Install and configure authentication starter with Livewire support

### Phase

Phase 0

### Description

Add a maintainable authentication foundation for the admin CMS, favoring a Laravel-native starter with Livewire compatibility.

### Deliverables

- authentication package installed
- login/logout/password flow scaffolded
- admin-only route protection baseline

### Dependencies

- `WB-001`
- `WB-005`

### Acceptance Criteria

- users can authenticate locally
- protected routes redirect unauthenticated users
- auth scaffolding is compatible with planned admin UX

### Suggested Files / Areas

- `composer.json`
- `routes/web.php`
- `app/Http/Controllers/` or `app/Livewire/`
- `resources/views/auth/`

### Validation

- `php artisan migrate`
- `php artisan test`
- manual auth flow validation

### Story Points

`3`

---

## WB-007

### Title

Install Flysystem S3 driver and configure Cloudflare R2

### Phase

Phase 0

### Description

Add and configure the storage driver needed for Cloudflare R2-backed media management.

### Deliverables

- S3-compatible Flysystem driver installed
- R2 disk configured
- upload/read/delete connectivity verified

### Dependencies

- `WB-003`
- `WB-004`

### Acceptance Criteria

- Laravel can write to the R2 disk
- Laravel can read from and delete from the R2 disk
- disk config is environment-driven

### Suggested Files / Areas

- `composer.json`
- `config/filesystems.php`
- `.env.example`

### Validation

- `php artisan tinker` storage smoke test
- manual validation against R2 bucket

### Story Points

`5`

---

## WB-008

### Title

Install and configure foundational support packages

### Phase

Phase 0

### Description

Install the minimum package layer for slugs and sitemap support, and document any intentionally deferred packages.

### Deliverables

- slugging package installed and configured
- sitemap package installed and configured
- deferred package decisions documented

### Dependencies

- `WB-001`

### Acceptance Criteria

- slug package is usable in Eloquent models
- sitemap package can be invoked in a test route, command, or service
- search and SEO package decisions are documented

### Suggested Files / Areas

- `composer.json`
- `config/`
- `app/Providers/`
- `docs/`

### Validation

- `php artisan test`
- manual package smoke test

### Story Points

`3`

---

## WB-009

### Title

Install and configure code quality and testing tools

### Phase

Phase 0

### Description

Set up Pint, Larastan, Pest, and supporting developer tooling for consistent quality enforcement.

### Deliverables

- Pest installed
- Pint configured
- Larastan configured
- baseline test and analysis commands documented

### Dependencies

- `WB-001`

### Acceptance Criteria

- `php artisan test` runs
- `./vendor/bin/pint --test` runs
- `./vendor/bin/phpstan analyse` runs with an initial config

### Suggested Files / Areas

- `composer.json`
- `phpstan.neon`
- `pint.json`
- `tests/`

### Validation

- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

### Story Points

`3`

---

## WB-010

### Title

Set up CI validation workflow

### Phase

Phase 0

### Description

Create an automated validation workflow for style, static analysis, and tests.

### Deliverables

- CI configuration
- quality gates for Pint, Larastan, Pest
- environment bootstrap for CI

### Dependencies

- `WB-009`

### Acceptance Criteria

- CI runs on pull requests or equivalent workflow events
- failing style or test checks block green status

### Suggested Files / Areas

- `.github/workflows/` or equivalent
- `composer.json`

### Validation

- CI dry run where supported
- push or local simulation if available

### Story Points

`5`

---

## WB-011

### Title

Create `.agent` structure and core agent documents

### Phase

Phase 0

### Description

Create the repository-scoped agent operating system for task execution, context loading, and reusable knowledge.

### Deliverables

- `.agent/INDEX.md`
- `.agent/MEMORY.md`
- `.agent/AGENT-HANDOVER.md`
- `.agent/TASK-WORKFLOW.md`
- `.agent/tasks/`

### Dependencies

- none

### Acceptance Criteria

- agents can locate startup instructions and task workflow
- task archive path exists
- reusable memory location exists

### Suggested Files / Areas

- `.agent/`

### Validation

- manual repository review

### Story Points

`3`

---

## WB-012

### Title

Create shared instructions, task templates, and agent handover workflow

### Phase

Phase 0

### Description

Define shared operating rules for task execution and continuation across agent sessions.

### Deliverables

- shared instructions
- current task template
- completed task pattern
- handover expectations

### Dependencies

- `WB-011`

### Acceptance Criteria

- task workflow is explicit
- task fields are standardized
- incomplete work has a defined handover path

### Suggested Files / Areas

- `.agent/agents/`
- `.agent/tasks/task-template.md`
- `.agent/AGENT-HANDOVER.md`

### Validation

- manual review against agent workflow rules

### Story Points

`2`

---

## WB-013

### Title

Create agent memory, knowledge base, and skill scaffolding

### Phase

Phase 0

### Description

Create durable documentation for product, architecture, SEO, AI-content, testing, and Laravel workflow guidance.

### Deliverables

- memory conventions
- knowledge base files
- skill files for Laravel, SEO, testing, media, and AI content

### Dependencies

- `WB-011`
- `WB-012`

### Acceptance Criteria

- stable knowledge is separated from task-specific notes
- skill files exist for key work categories
- context loading remains scoped and intentional

### Suggested Files / Areas

- `.agent/MEMORY.md`
- `.agent/knowledge-base/`
- `.agent/skills/`

### Validation

- manual review of agent docs

### Story Points

`3`

---

## WB-014

### Title

Create AGENTS.md, CLAUDE.md, and Copilot instructions

### Phase

Phase 0

### Description

Define editor and repository instructions for multiple coding-agent surfaces.

### Deliverables

- `AGENTS.md`
- Claude instruction file
- Copilot instruction file

### Dependencies

- `WB-011`

### Acceptance Criteria

- each supported agent surface has clear repository instructions
- instructions align with `.agent` workflow

### Suggested Files / Areas

- `AGENTS.md`
- `.agent/agents/CLAUDE.md`
- `.agent/agents/COPILOT.md`

### Validation

- manual review

### Story Points

`2`

---

## WB-015

### Title

Prepare production configuration and deployment baseline

### Phase

Phase 0

### Description

Define the minimum configuration needed for production deployment, scheduler, queue workers, and storage wiring.

### Deliverables

- production env checklist
- queue worker assumptions
- scheduler assumptions
- storage and cache deployment notes

### Dependencies

- `WB-004`
- `WB-007`

### Acceptance Criteria

- production-critical config is documented
- no major infrastructure dependency is undefined for MVP launch

### Suggested Files / Areas

- `docs/`
- `.env.example`
- deployment notes if present

### Validation

- manual review

### Story Points

`3`

---

## WB-016

### Title

Implement roles and permissions foundation

### Phase

Phase 1

### Description

Add the minimal role and permission model required for secure admin access and future expansion.

### Deliverables

- user role model
- permission checks or policies
- admin-only access enforcement

### Dependencies

- `WB-006`
- `WB-011`

### Acceptance Criteria

- unauthorized users cannot access admin routes
- role checks are centralized and testable

### Suggested Files / Areas

- `app/Models/User.php`
- `app/Policies/`
- `app/Providers/AuthServiceProvider.php`
- migrations

### Validation

- `php artisan test`
- manual authorization validation

### Story Points

`3`

---

## WB-017

### Title

Build admin layout, navigation, and shell

### Phase

Phase 1

### Description

Create the shared admin CMS layout used by dashboard and all management screens.

### Deliverables

- admin layout
- navigation
- common page shell
- auth-protected admin route group

### Dependencies

- `WB-005`
- `WB-006`
- `WB-016`

### Acceptance Criteria

- admin pages share a consistent layout
- navigation is extensible for future modules

### Suggested Files / Areas

- `app/Livewire/Admin/`
- `resources/views/`
- `routes/web.php`

### Validation

- manual browser validation

### Story Points

`5`

---

## WB-018

### Title

Build admin dashboard and settings foundation

### Phase

Phase 1

### Description

Create the initial dashboard and application settings area for operational visibility and configuration.

### Deliverables

- dashboard screen
- settings model or config persistence strategy
- basic site settings UI

### Dependencies

- `WB-017`

### Acceptance Criteria

- dashboard route exists
- settings can be stored and retrieved
- settings design allows future expansion

### Suggested Files / Areas

- `app/Livewire/Admin/Dashboard*`
- `app/Livewire/Admin/Settings*`
- config/services

### Validation

- `php artisan test`
- manual dashboard validation

### Story Points

`5`

---

## WB-019

### Title

Implement category database schema and model layer

### Phase

Phase 1

### Description

Create migrations, Eloquent models, and repository or service scaffolding for categories.

### Deliverables

- `categories` migration
- category model
- factory and tests scaffold

### Dependencies

- `WB-004`
- `WB-016`

### Acceptance Criteria

- categories table matches design requirements
- model supports slug, visibility, and ordering

### Suggested Files / Areas

- `database/migrations/`
- `app/Models/Category.php`
- `database/factories/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`3`

---

## WB-020

### Title

Build category CRUD and Livewire management screens

### Phase

Phase 1

### Description

Create category create, list, edit, delete, and validation workflows in the admin CMS.

### Deliverables

- category index screen
- create/edit forms
- validation rules
- feature tests

### Dependencies

- `WB-017`
- `WB-019`

### Acceptance Criteria

- admin can manage categories end to end
- slug uniqueness and validation are enforced

### Suggested Files / Areas

- `app/Livewire/Admin/Categories/`
- `app/Http/Requests/` or Livewire validation
- tests

### Validation

- `php artisan test`
- manual CRUD validation

### Story Points

`5`

---

## WB-021

### Title

Implement tag database schema and model layer

### Phase

Phase 1

### Description

Create migrations, models, and relationships for tags and post tagging support.

### Deliverables

- `tags` migration
- `post_tags` pivot migration
- tag model

### Dependencies

- `WB-004`

### Acceptance Criteria

- tags and pivot tables migrate successfully
- tag relationships are available from posts and tags

### Suggested Files / Areas

- `database/migrations/`
- `app/Models/Tag.php`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`3`

---

## WB-022

### Title

Build tag CRUD and admin assignment workflow

### Phase

Phase 1

### Description

Create tag management screens and prepare post editing flows to assign tags cleanly.

### Deliverables

- tag management UI
- validation rules
- tag selection component or workflow

### Dependencies

- `WB-017`
- `WB-021`

### Acceptance Criteria

- admin can create and manage tags
- tags can be selected in a reusable way for posts

### Suggested Files / Areas

- `app/Livewire/Admin/Tags/`
- tests

### Validation

- `php artisan test`
- manual tag management validation

### Story Points

`3`

---

## WB-023

### Title

Implement media database schema and service foundation

### Phase

Phase 1

### Description

Create the `media` table, model, and storage-aware service abstractions for uploaded assets.

### Deliverables

- media migration
- media model
- media service contracts

### Dependencies

- `WB-007`

### Acceptance Criteria

- media metadata can be stored in MySQL
- media service abstraction exists for upload/read/delete operations

### Suggested Files / Areas

- `database/migrations/`
- `app/Models/Media.php`
- `app/Services/Media/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`5`

---

## WB-024

### Title

Implement media upload pipeline with Cloudflare R2

### Phase

Phase 1

### Description

Build upload handling from admin UI through storage adapter and metadata persistence.

### Deliverables

- single upload flow
- multiple upload support
- R2 upload integration
- metadata persistence

### Dependencies

- `WB-017`
- `WB-023`

### Acceptance Criteria

- admin can upload one or many assets
- uploaded files are persisted to R2 and tracked in the database

### Suggested Files / Areas

- `app/Livewire/Admin/Media/`
- `app/Services/Media/`
- `config/filesystems.php`

### Validation

- manual upload validation
- `php artisan test`

### Story Points

`8`

---

## WB-025

### Title

Build media library, search, delete, and usage tracking

### Phase

Phase 1

### Description

Create the admin media browser and operational workflows around retrieval, searching, deletion, and usage awareness.

### Deliverables

- media library screen
- media search
- delete workflow
- usage tracking fields or checks

### Dependencies

- `WB-024`

### Acceptance Criteria

- admin can browse and search media
- deletions are safe and respect asset usage rules
- usage state is visible enough to avoid accidental broken references

### Suggested Files / Areas

- `app/Livewire/Admin/Media/`
- `app/Models/Media.php`
- tests

### Validation

- `php artisan test`
- manual media library validation

### Story Points

`8`

---

## WB-026

### Title

Implement template database schema and model layer

### Phase

Phase 1

### Description

Create the schema and models for templates and template blocks.

### Deliverables

- `templates` migration
- `template_blocks` migration
- template models and relationships

### Dependencies

- `WB-004`

### Acceptance Criteria

- templates and template blocks migrate successfully
- relationships support ordered template structure

### Suggested Files / Areas

- `database/migrations/`
- `app/Models/Template.php`
- `app/Models/TemplateBlock.php`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`5`

---

## WB-027

### Title

Build template CRUD and configuration screens

### Phase

Phase 1

### Description

Create admin workflows for managing templates and their editorial metadata.

### Deliverables

- template list/create/edit screens
- template metadata management
- validation rules

### Dependencies

- `WB-017`
- `WB-026`

### Acceptance Criteria

- admin can manage templates end to end
- template status and type can be controlled

### Suggested Files / Areas

- `app/Livewire/Admin/Templates/`
- tests

### Validation

- `php artisan test`
- manual template CRUD validation

### Story Points

`5`

---

## WB-028

### Title

Build template renderer and preview workflow

### Phase

Phase 1

### Description

Create the logic that converts template definitions into seeded post structures and previewable template output.

### Deliverables

- template renderer service
- template preview screen
- draft seeding flow

### Dependencies

- `WB-026`
- `WB-027`

### Acceptance Criteria

- a template can be previewed before use
- a template can seed a new draft structure predictably

### Suggested Files / Areas

- `app/Services/Templates/`
- `app/Livewire/Admin/Templates/`
- tests

### Validation

- `php artisan test`
- manual preview validation

### Story Points

`8`

---

## WB-029

### Title

Implement post database schema and model layer

### Phase

Phase 1

### Description

Create the `posts` table, relationships, publish-state fields, and supporting model behavior.

### Deliverables

- posts migration
- post model
- relationships to categories, templates, media, and tags

### Dependencies

- `WB-019`
- `WB-021`
- `WB-023`
- `WB-026`

### Acceptance Criteria

- posts schema matches the documented design
- publish-state fields support draft, scheduled, and published workflows

### Suggested Files / Areas

- `database/migrations/`
- `app/Models/Post.php`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`5`

---

## WB-030

### Title

Implement post block schema, models, and block type definitions

### Phase

Phase 1

### Description

Create the schema and model layer for structured post blocks and supported block types.

### Deliverables

- `post_blocks` migration
- block model
- block type enum or equivalent definitions

### Dependencies

- `WB-026`
- `WB-029`

### Acceptance Criteria

- supported block types are defined
- post blocks can be stored with deterministic ordering

### Suggested Files / Areas

- `database/migrations/`
- `app/Models/PostBlock.php`
- `app/Enums/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`5`

---

## WB-031

### Title

Build post editor foundation in Livewire

### Phase

Phase 1

### Description

Create the main Livewire-driven post editing experience for titles, metadata, and block management.

### Deliverables

- post create screen
- post edit screen
- form state handling
- draft persistence

### Dependencies

- `WB-017`
- `WB-029`
- `WB-030`

### Acceptance Criteria

- admin can create and edit draft posts
- editor state persists cleanly

### Suggested Files / Areas

- `app/Livewire/Admin/Posts/`
- `resources/views/`
- tests

### Validation

- `php artisan test`
- manual post editor validation

### Story Points

`8`

---

## WB-032

### Title

Implement text-oriented content block editors

### Phase

Phase 1

### Description

Add support for heading, paragraph, quote, list, and callout blocks.

### Deliverables

- block editors for text-oriented blocks
- serialization and validation rules
- rendering contracts

### Dependencies

- `WB-030`
- `WB-031`

### Acceptance Criteria

- supported text blocks can be created, edited, ordered, and saved
- invalid block payloads are rejected

### Suggested Files / Areas

- `app/Livewire/Admin/Posts/Blocks/`
- `app/Services/Posts/Blocks/`
- tests

### Validation

- `php artisan test`
- manual block editor validation

### Story Points

`8`

---

## WB-033

### Title

Implement image, code, and FAQ block editors

### Phase

Phase 1

### Description

Add support for image, code, and FAQ blocks with validation and storage-aware relationships where needed.

### Deliverables

- image block editor
- code block editor
- FAQ block editor

### Dependencies

- `WB-024`
- `WB-030`
- `WB-031`

### Acceptance Criteria

- image blocks can reference uploaded media
- code blocks preserve content safely
- FAQ blocks can capture structured question-answer pairs

### Suggested Files / Areas

- `app/Livewire/Admin/Posts/Blocks/`
- tests

### Validation

- `php artisan test`
- manual block editor validation

### Story Points

`8`

---

## WB-034

### Title

Implement draft workflow, scheduling, and publishing transitions

### Phase

Phase 1

### Description

Create the state transition logic for draft, scheduled, published, and unpublished content.

### Deliverables

- publish service
- schedule workflow
- unpublish workflow
- transition validation

### Dependencies

- `WB-029`
- `WB-031`

### Acceptance Criteria

- invalid publish transitions are blocked
- scheduled posts store future publish intent correctly
- only published posts are visible publicly

### Suggested Files / Areas

- `app/Services/Posts/`
- `app/Actions/Posts/`
- tests

### Validation

- `php artisan test`
- manual publish flow validation

### Story Points

`5`

---

## WB-035

### Title

Implement post classification and featured image assignment

### Phase

Phase 1

### Description

Add category, tag, and featured image selection workflows to the post editor.

### Deliverables

- category assignment
- tag assignment
- featured image selection

### Dependencies

- `WB-020`
- `WB-022`
- `WB-025`
- `WB-031`

### Acceptance Criteria

- post editor supports selecting and updating classifications
- featured image can be set and changed safely

### Suggested Files / Areas

- `app/Livewire/Admin/Posts/`
- tests

### Validation

- `php artisan test`
- manual post assignment validation

### Story Points

`3`

---

## WB-036

### Title

Implement knowledge base schema and model layer

### Phase

Phase 1

### Description

Create the database and model foundation for internal knowledge base entries.

### Deliverables

- knowledge base migration
- knowledge entry model
- relationships to media and SEO metadata

### Dependencies

- `WB-023`

### Acceptance Criteria

- knowledge entries can be persisted with status and type
- schema supports internal editorial reference material

### Suggested Files / Areas

- `database/migrations/`
- `app/Models/KnowledgeBaseEntry.php`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`3`

---

## WB-037

### Title

Build knowledge base CRUD, search, tagging, and categorization

### Phase

Phase 1

### Description

Create admin workflows for managing and retrieving knowledge base content.

### Deliverables

- CRUD screens
- search
- tags or categories for knowledge entries
- validation

### Dependencies

- `WB-017`
- `WB-036`

### Acceptance Criteria

- admin can manage knowledge entries end to end
- entries can be searched and organized

### Suggested Files / Areas

- `app/Livewire/Admin/KnowledgeBase/`
- tests

### Validation

- `php artisan test`
- manual knowledge base validation

### Story Points

`5`

---

## WB-038

### Title

Implement SEO metadata schema and SEO domain services

### Phase

Phase 1

### Description

Create the `seo_metadata` table, models, and service layer for title, description, canonical, and robots management.

### Deliverables

- SEO metadata migration
- model and relationships
- SEO save/read services

### Dependencies

- `WB-019`
- `WB-029`
- `WB-036`

### Acceptance Criteria

- SEO metadata can be attached to posts and categories
- canonical and robots fields are supported

### Suggested Files / Areas

- `database/migrations/`
- `app/Models/SeoMetadata.php`
- `app/Services/Seo/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`5`

---

## WB-039

### Title

Implement Open Graph, Twitter, canonical, and structured data rendering

### Phase

Phase 1

### Description

Build the runtime output layer for social metadata, canonicals, and schema generation.

### Deliverables

- OG tag output
- Twitter card output
- canonical output
- article and breadcrumb schema

### Dependencies

- `WB-038`
- `WB-029`
- `WB-023`

### Acceptance Criteria

- published pages output consistent SEO tags
- schema is derived from content and metadata, not manually duplicated

### Suggested Files / Areas

- `app/Services/Seo/`
- `resources/views/`
- public layout templates

### Validation

- manual source inspection
- `php artisan test`

### Story Points

`5`

---

## WB-040

### Title

Build public website shell and static public pages

### Phase

Phase 1

### Description

Create the public-facing site layout and foundational pages such as homepage, about, contact, and shared navigation.

### Deliverables

- public layout
- homepage
- about page
- contact page

### Dependencies

- `WB-017`

### Acceptance Criteria

- public site has consistent layout and navigation
- static pages render correctly and are SEO-ready

### Suggested Files / Areas

- `app/Livewire/Frontend/`
- `resources/views/`
- `routes/web.php`

### Validation

- manual browser validation

### Story Points

`5`

---

## WB-041

### Title

Build category, post, and author public pages

### Phase

Phase 1

### Description

Create the primary public read paths for articles, categories, and the primary author.

### Deliverables

- category archive page
- post detail page
- author page for Amit Kumar Sharma

### Dependencies

- `WB-020`
- `WB-029`
- `WB-034`
- `WB-039`
- `WB-040`

### Acceptance Criteria

- only published posts are publicly visible
- category pages list published content
- author page displays author profile and article list

### Suggested Files / Areas

- `app/Livewire/Frontend/`
- query services
- views

### Validation

- manual browser validation
- `php artisan test`

### Story Points

`8`

---

## WB-042

### Title

Implement search indexing and query service

### Phase

Phase 1

### Description

Create the application-side search foundation for posts, media, or knowledge content, defaulting to MySQL-native search unless stronger tooling is needed.

### Deliverables

- search query service
- indexing strategy
- search relevance assumptions documented

### Dependencies

- `WB-029`
- `WB-036`

### Acceptance Criteria

- published posts can be searched
- search implementation is simple and maintainable for MVP

### Suggested Files / Areas

- `app/Services/Search/`
- models
- migrations if full-text indexes are added

### Validation

- `php artisan test`
- manual search smoke test

### Story Points

`5`

---

## WB-043

### Title

Build public search UI and results page

### Phase

Phase 1

### Description

Create the public search interface and render search results using the query service.

### Deliverables

- search input UI
- search results page
- empty and no-result states

### Dependencies

- `WB-040`
- `WB-042`

### Acceptance Criteria

- public users can search and view results
- search results only expose public content

### Suggested Files / Areas

- `app/Livewire/Frontend/Search*`
- views

### Validation

- manual browser validation
- `php artisan test`

### Story Points

`3`

---

## WB-044

### Title

Implement technical SEO outputs: sitemap, RSS, robots.txt, and breadcrumbs

### Phase

Phase 1

### Description

Build the remaining public SEO infrastructure required for launch readiness.

### Deliverables

- sitemap generation
- RSS feed
- robots.txt route or file strategy
- breadcrumb rendering and schema support

### Dependencies

- `WB-008`
- `WB-039`
- `WB-041`

### Acceptance Criteria

- sitemap contains published content only
- RSS exposes the intended article feed
- robots policy matches public/admin boundaries
- breadcrumbs render consistently on public pages

### Suggested Files / Areas

- `app/Console/Commands/`
- `routes/web.php`
- `resources/views/`
- `app/Services/Seo/`

### Validation

- manual source inspection
- `php artisan test`

### Story Points

`5`

---

## WB-045

### Title

Add MVP end-to-end validation and launch hardening

### Phase

Phase 1

### Description

Add the minimum cross-module tests and validation workflows required to launch the MVP blog platform confidently.

### Deliverables

- critical feature tests
- publish-flow validation
- public rendering smoke tests
- launch checklist inputs

### Dependencies

- `WB-020`
- `WB-025`
- `WB-028`
- `WB-034`
- `WB-041`
- `WB-044`

### Acceptance Criteria

- core publish flows are covered by tests
- MVP launch-critical regressions are detectable

### Suggested Files / Areas

- `tests/Feature/`
- `tests/Unit/`
- launch checklist docs

### Validation

- `php artisan test`
- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`

### Story Points

`8`

---

## WB-046

### Title

Implement AI provider abstraction layer

### Phase

Phase 2

### Description

Create the core contracts and service boundaries for provider-agnostic AI execution.

### Deliverables

- provider contracts
- orchestration entry point
- normalized result shape

### Dependencies

- `WB-004`
- `WB-029`
- `WB-038`

### Acceptance Criteria

- the app can call AI providers through abstractions rather than direct vendor coupling
- text generation use cases can target a stable internal contract

### Suggested Files / Areas

- `app/Infrastructure/Ai/`
- `app/Services/Ai/`

### Validation

- `php artisan test`
- contract-level unit tests

### Story Points

`8`

---

## WB-047

### Title

Implement OpenAI provider integration

### Phase

Phase 2

### Description

Add the first concrete AI provider implementation for text-oriented workflows.

### Deliverables

- OpenAI gateway
- config and environment keys
- testable provider adapter

### Dependencies

- `WB-046`

### Acceptance Criteria

- app can execute provider calls through the abstraction layer
- provider-specific details do not leak into domain services

### Suggested Files / Areas

- `app/Infrastructure/Ai/Providers/OpenAi/`
- config/services

### Validation

- `php artisan test`
- integration smoke test where feasible

### Story Points

`5`

---

## WB-048

### Title

Implement Anthropic and Gemini provider integrations

### Phase

Phase 2

### Description

Add secondary provider implementations to prove multi-provider architecture and future flexibility.

### Deliverables

- Anthropic gateway
- Gemini gateway
- provider config wiring

### Dependencies

- `WB-046`

### Acceptance Criteria

- providers can be selected or swapped through the shared abstraction
- provider-specific configs are isolated cleanly

### Suggested Files / Areas

- `app/Infrastructure/Ai/Providers/Anthropic/`
- `app/Infrastructure/Ai/Providers/Gemini/`

### Validation

- `php artisan test`
- adapter-level integration smoke tests where feasible

### Story Points

`8`

---

## WB-049

### Title

Implement prompt management schema and admin workflows

### Phase

Phase 2

### Description

Create storage and management for prompt templates, versions, and categories.

### Deliverables

- prompt template schema
- prompt versioning strategy
- admin management screens

### Dependencies

- `WB-017`
- `WB-046`

### Acceptance Criteria

- prompts can be stored, categorized, and versioned
- prompt changes are not hidden in code-only locations

### Suggested Files / Areas

- migrations
- `app/Models/`
- `app/Livewire/Admin/Prompts/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`5`

---

## WB-050

### Title

Implement topic management schema and approval workflow

### Phase

Phase 2

### Description

Create topic records, statuses, and the admin approval lifecycle for suggested topics.

### Deliverables

- `topics` migration
- topic statuses: suggested, approved, rejected, used
- topic queue screens

### Dependencies

- `WB-017`
- `WB-049`

### Acceptance Criteria

- topics can be suggested, reviewed, approved, rejected, and marked used
- topics can map to categories or clusters

### Suggested Files / Areas

- `database/migrations/`
- `app/Models/Topic.php`
- `app/Livewire/Admin/Topics/`

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`5`

---

## WB-051

### Title

Build topic discovery agent workflow

### Phase

Phase 2

### Description

Create the first AI-assisted workflow for generating topic suggestions and mapping them into the topic queue.

### Deliverables

- discovery service
- topic suggestion job
- category or cluster mapping logic

### Dependencies

- `WB-046`
- `WB-047`
- `WB-050`

### Acceptance Criteria

- discovery output creates topic suggestions, not published content
- suggestions are reviewable before use

### Suggested Files / Areas

- `app/Services/Ai/Discovery/`
- jobs
- topic admin flows

### Validation

- `php artisan test`
- manual workflow validation

### Story Points

`8`

---

## WB-052

### Title

Build content blueprint engine

### Phase

Phase 2

### Description

Create the service that converts approved topics into structured blueprints using templates, knowledge base inputs, and prompt context.

### Deliverables

- blueprint generation service
- structure definition rules
- template mapping logic

### Dependencies

- `WB-028`
- `WB-037`
- `WB-050`

### Acceptance Criteria

- an approved topic can produce a structured blueprint
- blueprint output can inform draft generation cleanly

### Suggested Files / Areas

- `app/Services/Ai/Blueprints/`
- DTOs
- tests

### Validation

- `php artisan test`

### Story Points

`8`

---

## WB-053

### Title

Build content generation agent for outlines and article drafts

### Phase

Phase 2

### Description

Create the content generation workflow that produces outlines and article draft content using approved topics and blueprints.

### Deliverables

- outline generation
- article draft generation
- draft persistence into posts and post blocks

### Dependencies

- `WB-046`
- `WB-047`
- `WB-052`
- `WB-029`
- `WB-030`

### Acceptance Criteria

- generated output is stored as draft content only
- output is structured enough for editor review and revision

### Suggested Files / Areas

- `app/Services/Ai/Generation/`
- jobs
- post services

### Validation

- `php artisan test`
- manual draft generation validation

### Story Points

`13`

---

## WB-054

### Title

Add FAQ, SEO, and tag generation support to AI content workflows

### Phase

Phase 2

### Description

Extend generation workflows so AI can propose FAQ blocks, metadata, and tags as draft suggestions.

### Deliverables

- FAQ generation
- SEO suggestion generation
- tag suggestion generation

### Dependencies

- `WB-038`
- `WB-053`

### Acceptance Criteria

- AI-generated SEO and tag suggestions are reviewable and editable
- FAQ output can map to supported block structures

### Suggested Files / Areas

- `app/Services/Ai/Generation/`
- `app/Services/Seo/`
- block services

### Validation

- `php artisan test`
- manual suggestion review validation

### Story Points

`8`

---

## WB-055

### Title

Integrate draft approval gates and scheduled AI workflows

### Phase

Phase 2

### Description

Connect AI outputs to draft workflows, scheduler jobs, and explicit approval gates without enabling direct publish behavior.

### Deliverables

- scheduled topic discovery
- scheduled content generation hooks
- draft approval gate enforcement

### Dependencies

- `WB-004`
- `WB-051`
- `WB-053`

### Acceptance Criteria

- scheduled AI tasks run through queues and scheduler
- generated content never skips review state

### Suggested Files / Areas

- `app/Console/`
- jobs
- services

### Validation

- `php artisan schedule:list`
- `php artisan test`

### Story Points

`5`

---

## WB-056

### Title

Implement AI job tracking and retry handling

### Phase

Phase 2

### Description

Create persistent tracking for AI jobs, statuses, attempts, and retry-safe execution.

### Deliverables

- `ai_jobs` schema
- status tracking
- retry handling
- job admin visibility

### Dependencies

- `WB-046`
- `WB-053`

### Acceptance Criteria

- AI jobs are persisted with state changes
- failed jobs can be retried safely
- job history is inspectable

### Suggested Files / Areas

- migrations
- `app/Models/AiJob.php`
- jobs
- admin screens

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`8`

---

## WB-057

### Title

Implement AI cost tracking and reporting

### Phase

Phase 2

### Description

Capture model usage, token counts, and cost data for AI workflows.

### Deliverables

- `ai_job_costs` schema
- token usage capture
- cost calculation or storage
- basic reporting view

### Dependencies

- `WB-048`
- `WB-056`

### Acceptance Criteria

- AI calls can store usage and cost data
- cost data can be queried by job or provider

### Suggested Files / Areas

- migrations
- `app/Models/AiJobCost.php`
- services
- admin views

### Validation

- `php artisan migrate`
- `php artisan test`

### Story Points

`5`

---

## WB-058

### Title

Implement AI image generation pipeline

### Phase

Phase 3

### Description

Create the workflow for generating image candidates, storing them in R2, and attaching them as reviewable media.

### Deliverables

- image generation service
- prompt capture
- media persistence for generated images

### Dependencies

- `WB-024`
- `WB-046`
- `WB-056`

### Acceptance Criteria

- generated images are stored as media records
- generated assets require human selection before use on published posts

### Suggested Files / Areas

- `app/Services/Ai/Images/`
- jobs
- media services

### Validation

- `php artisan test`
- manual image flow validation

### Story Points

`8`

---

## WB-059

### Title

Implement stock image integrations and attribution tracking

### Phase

Phase 3

### Description

Add stock image sourcing workflows for Pexels, Unsplash, or equivalent providers and track usage and attribution.

### Deliverables

- provider integration service
- search and select flow
- attribution persistence

### Dependencies

- `WB-023`
- `WB-025`

### Acceptance Criteria

- admin can search and import stock images
- attribution data is stored when required

### Suggested Files / Areas

- `app/Services/Media/Stock/`
- admin UI
- migrations if needed

### Validation

- `php artisan test`
- manual stock import validation

### Story Points

`8`

---

## WB-060

### Title

Implement analytics data collection foundation

### Phase

Phase 3

### Description

Create the tracking model and event capture needed for content, author, and traffic reporting.

### Deliverables

- analytics event model or storage strategy
- content performance capture
- author-level reporting inputs

### Dependencies

- `WB-041`

### Acceptance Criteria

- content interaction metrics can be captured or ingested
- data model supports future dashboards

### Suggested Files / Areas

- migrations
- `app/Models/`
- services

### Validation

- `php artisan test`
- manual instrumentation review

### Story Points

`5`

---

## WB-061

### Title

Build analytics dashboards for content and authors

### Phase

Phase 3

### Description

Create admin-facing dashboards for content analytics, author analytics, and traffic reporting.

### Deliverables

- content analytics dashboard
- author analytics dashboard
- reporting views

### Dependencies

- `WB-060`

### Acceptance Criteria

- dashboards display useful, non-empty metrics
- data is grouped in a way editors can act on

### Suggested Files / Areas

- `app/Livewire/Admin/Analytics/`
- query services
- views

### Validation

- `php artisan test`
- manual dashboard validation

### Story Points

`8`

---

## WB-062

### Title

Build SEO dashboard and scoring engine

### Phase

Phase 3

### Description

Implement the editorial SEO scoring system and admin dashboard described in the SEO strategy.

### Deliverables

- SEO score service
- SEO dashboard
- missing metadata detection
- recommendations display

### Dependencies

- `WB-038`
- `WB-039`
- `WB-044`

### Acceptance Criteria

- articles receive a transparent SEO score
- missing or weak SEO elements are surfaced in admin

### Suggested Files / Areas

- `app/Services/Seo/Scoring/`
- `app/Livewire/Admin/Seo/`
- tests

### Validation

- `php artisan test`
- manual score validation

### Story Points

`8`

---

## WB-063

### Title

Build internal linking engine and recommendations

### Phase

Phase 3

### Description

Create the content relationship analysis and recommendation system for internal links.

### Deliverables

- related content suggestion service
- link recommendation workflow
- orphan detection baseline

### Dependencies

- `WB-037`
- `WB-042`
- `WB-062`

### Acceptance Criteria

- the system can recommend relevant internal links for drafts or refreshes
- orphaned or weakly connected pages can be identified

### Suggested Files / Areas

- `app/Services/Seo/InternalLinking/`
- admin UI
- tests

### Validation

- `php artisan test`
- manual recommendation review

### Story Points

`8`

---

## WB-064

### Title

Build content refresh engine

### Phase

Phase 3

### Description

Create the workflow for detecting stale content and recommending refresh actions based on age, performance, and cluster needs.

### Deliverables

- stale content detection
- refresh recommendation logic
- admin refresh queue or view

### Dependencies

- `WB-060`
- `WB-062`
- `WB-063`

### Acceptance Criteria

- aging or underperforming content can be flagged for review
- refresh suggestions are actionable enough for editors

### Suggested Files / Areas

- `app/Services/ContentRefresh/`
- admin UI
- query services

### Validation

- `php artisan test`
- manual refresh workflow validation

### Story Points

`8`

---

## Recommended Backlog Usage

- execute tasks roughly in ID order unless dependency analysis justifies a change
- do not parallelize tasks that touch the same core migration or editor flow unless coordination is explicit
- convert each task into one working session or one pull request when practical
- preserve task IDs in branches, commits, or PR descriptions where useful

## Suggested Initial Milestone Cuts

### Milestone A: Foundation Ready

- `WB-001` to `WB-015`

### Milestone B: MVP CMS Ready

- `WB-016` to `WB-039`

### Milestone C: Public Launch Ready

- `WB-040` to `WB-045`

### Milestone D: AI Editorial Foundation

- `WB-046` to `WB-057`

### Milestone E: Advanced Publishing Intelligence

- `WB-058` to `WB-064`

## Summary

This backlog is designed to take Wide Web Blog from Laravel foundation setup through MVP launch, AI-assisted publishing, and advanced editorial capabilities. The ordering intentionally favors a fast, trustworthy launch with strong SEO and content operations before deeper automation and intelligence features are layered in.
