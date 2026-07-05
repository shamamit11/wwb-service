# Service Tasks: Article Videos MVP

## Document Purpose

This document translates `../WWB_ARTICLE_VIDEO_MVP.md` into a service-only implementation backlog for `widewebblog/service`.

It covers:

- database and model integration
- AI recommendation and draft workflows
- queue and scheduler work
- TTS, FFmpeg, and R2 asset handling
- admin-facing service APIs
- observability, failure handling, and testing

It does not include:

- Admin UI screens or UX flows
- Livewire or Blade work
- public frontend work
- auto-publishing to Instagram, YouTube Shorts, or X

## Service Integration Assumptions

- `Post` is the source article entity for Article Videos.
- AI generation must remain draft-first and require admin approval before rendering or any downstream publishing.
- recommendation and draft generation should reuse the existing `ai_jobs`, `ai_generation_steps`, and prompt-template patterns where practical.
- generated media should be stored on the existing R2 disk under `article-videos/{article_video_id}/...`.
- long-running generation and render work should be queue-backed and retry-safe.

## Epic AV-SVC-1 - Domain Foundation

### Task AV-SVC-1.1 - Create article video persistence schema

Subtasks:

- add the `article_video_recommendations` migration with indexes on `post_id`, `status`, and `evaluated_at`
- add the `article_videos` migration with indexes on `post_id`, `status`, and `approved_at`
- add the `article_video_memory_entries` migration with indexes on `post_id` and `created_at`
- choose foreign-key strategy against `posts` and nullable linkages for recommendations and generated videos
- ensure only asset paths and structured JSON fields are persisted, never binary media blobs

Acceptance criteria:

- all three tables exist with the MVP fields
- foreign keys and indexes support list, status, and recent-history queries
- JSON and datetime fields are castable in Eloquent

### Task AV-SVC-1.2 - Add models, enums, and relations

Subtasks:

- create `ArticleVideoRecommendation`, `ArticleVideo`, and `ArticleVideoMemoryEntry` models
- add status and render-mode enums or model constants aligned with the MVP states
- add `Post` relations for recommendations, videos, and memory entries where needed
- add inverse relations between video, recommendation, and source post
- add simple state helper methods such as `isDraft`, `isApproved`, `isRendered`, and `canRender`

Acceptance criteria:

- service code can navigate all required relationships without raw joins
- status values are centralized and reusable across requests, services, and tests

### Task AV-SVC-1.3 - Add repository/query support

Subtasks:

- create focused repositories or query services for recommendations, videos, and memory entries
- add helper queries for active draft lookup, rendered-video existence, and recent memory fetches
- add idempotent upsert support for recommendation refreshes

Acceptance criteria:

- workflow services can read and write Article Video data through reusable query abstractions
- duplicate recommendation records are prevented by design

## Epic AV-SVC-2 - Config And Infrastructure Wiring

### Task AV-SVC-2.1 - Add Article Videos configuration

Subtasks:

- add `article_videos` config for weekly limits, lookback window, render mode, and storage path conventions
- wire model and voice defaults into existing OpenAI configuration patterns instead of scattering env reads
- add FFmpeg-related config such as binary path, temporary working directory, and timeout values if required
- update `.env.example` only for new service-side keys

Acceptance criteria:

- all runtime knobs referenced by the MVP live in config, not hard-coded service classes
- missing config fails early with actionable errors

### Task AV-SVC-2.2 - Register queue and retry behavior

Subtasks:

- confirm `ai` queue usage for recommendation and draft generation jobs
- add a separate `video-render` queue for TTS and FFmpeg workloads
- define retry counts, backoff, and timeout behavior for AI-provider calls and render jobs
- document required worker processes for local and production execution

Acceptance criteria:

- queue assignments match workload profiles
- retries are safe and do not duplicate video records or assets

## Epic AV-SVC-3 - Recommendation Workflow

### Task AV-SVC-3.1 - Build candidate selection pipeline

Subtasks:

- query published posts within the configurable lookback window
- exclude posts that already have rendered videos
- exclude posts with recently skipped or rejected recommendations when the business rule applies
- trim article payloads to a prompt-safe size before AI submission
- cap candidates based on weekly recommendation limits

Acceptance criteria:

- candidate selection is deterministic, configurable, and efficient
- the recommendation agent receives only relevant published articles

### Task AV-SVC-3.2 - Implement recommendation agent integration

Subtasks:

- create an `ArticleVideoRecommendationAgent` aligned with existing agent contracts
- add or seed a prompt-template family for recommendation generation
- define a strict JSON schema for score, priority, format, reason, hook, and risk note
- validate and normalize AI output before persistence
- record AI job and generation-step metadata for each run

Acceptance criteria:

- recommendation generation is provider-agnostic through the existing AI abstraction
- invalid or partial AI output fails safely without corrupting domain state

### Task AV-SVC-3.3 - Persist and refresh recommendations

Subtasks:

- upsert pending recommendations for the same post instead of duplicating active rows
- mark selection state transitions cleanly between `pending`, `selected`, `skipped`, and `rejected`
- persist evaluation timestamps and any prompt/input metadata needed for debugging
- save memory-relevant hook candidates when useful for later deduplication

Acceptance criteria:

- manual recommendation refresh does not create noisy duplicates
- recommendation lifecycle changes remain queryable and auditable

### Task AV-SVC-3.4 - Add manual and scheduled orchestration

Subtasks:

- add `php artisan article-videos:recommend`
- keep the command orchestration-only and move business logic into services
- add scheduler integration for the weekly recommendation pass if approved by service ops
- ensure the command is safe to run more than once per period

Acceptance criteria:

- recommendations can be triggered manually and by scheduler without duplicate side effects

## Epic AV-SVC-4 - Draft Generation Workflow

### Task AV-SVC-4.1 - Create draft bootstrap flow

Subtasks:

- create a service method that starts an `ArticleVideo` draft from a selected recommendation or a direct post choice
- prevent more than one active draft/rendering record for the same post unless the old one is terminal
- attach the originating recommendation when applicable
- create or reuse AI job tracking for the draft-generation run

Acceptance criteria:

- draft creation is idempotent for repeated admin actions
- source post and recommendation linkage is preserved

### Task AV-SVC-4.2 - Implement draft agent integration

Subtasks:

- create an `ArticleVideoDraftAgent` aligned with the current agent architecture
- add or seed a prompt-template family for Article Video script generation
- include recent video-memory entries in the prompt context
- require strict JSON output for hook, voiceover text, scenes, captions, and script metadata
- validate the voiceover length and scene structure before saving

Acceptance criteria:

- generated draft data is structured, validated, and storable without manual cleanup
- prompt and response handling stays consistent with other AI workflows in the service

### Task AV-SVC-4.3 - Support draft regeneration

Subtasks:

- allow regeneration only for non-approved videos
- replace draft payload fields atomically so partial data is not left behind
- append to memory only when a draft is accepted or rendered, not on every failed regeneration
- ensure regeneration does not auto-approve or auto-render

Acceptance criteria:

- admins can retry script generation safely before approval
- regeneration preserves a single authoritative draft record

## Epic AV-SVC-5 - Approval And Status Management

### Task AV-SVC-5.1 - Implement recommendation actions

Subtasks:

- add service actions for `select`, `skip`, and `reject` on recommendations
- enforce only one selected recommendation per post when required
- prevent selection of stale or terminal recommendations when a video already exists

Acceptance criteria:

- recommendation state transitions are explicit and guarded by business rules

### Task AV-SVC-5.2 - Implement video draft actions

Subtasks:

- add approve, reject, and optional move-back-to-draft actions for videos
- stamp `approved_at` on approval
- block render dispatch unless the video is approved
- prevent editing of approved data unless the workflow explicitly reopens it

Acceptance criteria:

- status transitions match the MVP lifecycle exactly
- rejected videos cannot enter the render pipeline

## Epic AV-SVC-6 - Render, TTS, And Asset Storage

### Task AV-SVC-6.1 - Build storage service

Subtasks:

- create `ArticleVideoStorageService` for path generation, uploads, and existence checks
- standardize asset keys for script JSON, MP3, SRT, thumbnail, and final MP4
- add cleanup or overwrite rules for regeneration and rerender scenarios
- return storage paths and URLs through a single abstraction

Acceptance criteria:

- no workflow writes directly to ad hoc R2 paths
- storage rules are centralized and testable

### Task AV-SVC-6.2 - Generate TTS and caption assets

Subtasks:

- synthesize `voiceover_text` to MP3 using the configured OpenAI TTS model and voice
- generate caption output from the approved draft payload
- upload generated assets to R2 and persist their paths
- record provider failures with enough detail for retry diagnosis

Acceptance criteria:

- approved videos can produce reusable voiceover and caption assets without manual intervention

### Task AV-SVC-6.3 - Render vertical MP4 with FFmpeg

Subtasks:

- compose a text-only vertical video from the approved script, captions, and voiceover
- handle local temporary files and cleanup after upload
- upload the final MP4 and optional thumbnail to R2
- persist `video_path`, `thumbnail_path`, `duration_seconds`, `rendered_at`, and `status`
- capture failure messages and move status to `failed` on permanent errors

Acceptance criteria:

- a successful render produces a downloadable MP4 in R2
- failed renders are visible and retryable without corrupting prior assets

### Task AV-SVC-6.4 - Implement render job orchestration

Subtasks:

- add `RenderArticleVideoJob` on the `video-render` queue
- make the job rehydrate by ID and guard against duplicate concurrent renders
- split provider failures from validation failures for retry behavior
- optionally break TTS and FFmpeg into separate internal steps if operational visibility requires it

Acceptance criteria:

- render jobs are idempotent and safe under retries
- long-running work stays off synchronous request paths

## Epic AV-SVC-7 - Admin-Facing Service API

### Task AV-SVC-7.1 - Add recommendation endpoints

Subtasks:

- add admin API endpoints to list recommendations, show recommendation detail, and trigger recommendation generation
- add action endpoints for select, skip, and reject
- support filtering by status, date window, score, and source post where useful
- return resources shaped for admin consumption without embedding UI concerns

Acceptance criteria:

- the admin app can retrieve and mutate recommendation state through stable service endpoints

### Task AV-SVC-7.2 - Add Article Video endpoints

Subtasks:

- add endpoints to create a draft, regenerate a draft, approve, reject, render, retry failed render, and fetch detail
- add listing endpoints for draft, approved, rendering, rendered, failed, and rejected states
- expose preview and download metadata as URLs or signed URLs from the storage abstraction
- validate all mutation payloads with FormRequests and map them into DTOs

Acceptance criteria:

- the full non-UI lifecycle is operable from the service API alone
- controller logic remains thin and workflow-oriented

### Task AV-SVC-7.3 - Add resources and API documentation

Subtasks:

- create API resources for recommendations and videos
- document request and response contracts in Scramble or the repo's API docs flow
- keep error payloads consistent for validation, conflict, and failure states

Acceptance criteria:

- service contracts are documented and testable without inspecting implementation details

## Epic AV-SVC-8 - Observability, Memory, And Auditability

### Task AV-SVC-8.1 - Integrate workflow tracking

Subtasks:

- decide which Article Video operations create `ai_jobs`
- record `ai_generation_steps` for recommendation and draft agent executions
- persist render lifecycle metadata either on `article_videos` or a supporting job-tracking structure
- add actionable logging around provider, storage, and render failures

Acceptance criteria:

- operators can trace recommendation, draft, and render failures end-to-end

### Task AV-SVC-8.2 - Implement memory and deduplication rules

Subtasks:

- create `ArticleVideoMemoryService`
- query recent hooks, angles, and voiceovers for prompt conditioning
- add duplicate or near-duplicate guardrails for repeated hooks and openings
- define when a memory entry is written so low-quality rejected generations do not pollute memory

Acceptance criteria:

- repeated hooks and angles are reduced through explicit service logic

### Task AV-SVC-8.3 - Add audit coverage where needed

Subtasks:

- determine whether recommendation selection, approval, rejection, and render retry need activity logs
- add audit events only for state changes that matter operationally or editorially
- keep logs attributable to the acting admin user where the request context provides one

Acceptance criteria:

- sensitive lifecycle changes are traceable without excessive noise

## Epic AV-SVC-9 - Testing And Release Readiness

### Task AV-SVC-9.1 - Add automated coverage

Subtasks:

- add migration and model relation tests
- add workflow tests for recommendation generation, draft generation, approval, and render dispatch rules
- add failure-path tests for invalid AI JSON, TTS failure, FFmpeg failure, and duplicate render protection
- add API feature tests for lifecycle endpoints and conflict states

Acceptance criteria:

- the core Article Video lifecycle is covered at model, workflow, and API levels

### Task AV-SVC-9.2 - Add operational verification steps

Subtasks:

- document required local tools such as FFmpeg availability
- define a minimal validation flow for staging or local smoke tests
- confirm queue worker, scheduler, and R2 permissions needed for the feature
- add release notes for new env vars and worker queues

Acceptance criteria:

- the service team has a clear checklist to enable the feature outside development

## Suggested Delivery Order

1. `AV-SVC-1` Domain Foundation
2. `AV-SVC-2` Config And Infrastructure Wiring
3. `AV-SVC-3` Recommendation Workflow
4. `AV-SVC-4` Draft Generation Workflow
5. `AV-SVC-5` Approval And Status Management
6. `AV-SVC-6` Render, TTS, And Asset Storage
7. `AV-SVC-7` Admin-Facing Service API
8. `AV-SVC-8` Observability, Memory, And Auditability
9. `AV-SVC-9` Testing And Release Readiness
