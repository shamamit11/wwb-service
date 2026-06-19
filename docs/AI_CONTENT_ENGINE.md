# AI Content Engine

## Purpose

This document explains the AI content engine that currently ships in the `widewebblog/service` repository.

Phase 3 is an editorial acceleration layer. It helps staff discover topics, create briefs, and draft posts faster, but it does not replace human approval.

## MVP Rules

- AI never publishes posts directly.
- Topic discovery creates suggested topics only.
- Only approved topics can generate content briefs.
- Only approved content briefs can generate blog drafts.
- Generated posts remain drafts until an admin reviews and publishes them through the normal post lifecycle.
- Image generation is not part of this phase. AI may suggest image ideas, placement notes, and alt text, but image creation and selection stay manual.

## MVP Flow

```mermaid
flowchart LR
    A["Knowledge Base Context"] --> B["TopicDiscoveryAgent"]
    B --> C["Suggested Topics"]
    C --> D["Admin Approval"]
    D --> E["ContentBriefAgent"]
    E --> F["Content Briefs"]
    F --> G["Admin Approval"]
    G --> H["BlogWriterAgent"]
    H --> I["Draft Posts"]
    I --> J["Human Review And Publish"]
```

## Main Building Blocks

### Knowledge Base Context

Knowledge Base entries provide grounding context for agents. The current service uses `KnowledgeContextService` to:

- search active entries
- apply optional metadata filters
- format prompt-safe context with size limits

This context supports topic discovery and content brief generation. It is used as reference material, not as content that AI may overwrite.

### Agents

The MVP includes three agents:

- `TopicDiscoveryAgent`
- `ContentBriefAgent`
- `BlogWriterAgent`

These agents operate through workflow services and internal tools rather than directly mutating domain state on their own.

### Internal AI Tools

The current internal tool set supports:

- duplicate topic checks
- saving suggested topics
- saving content briefs
- saving post drafts
- searching existing posts
- finding internal links

These tools help keep persistence logic inside services and repositories instead of inside prompts or agent classes.

### Workflow Orchestration

`AiWorkflowOrchestrator` coordinates the three workflow paths:

- topic discovery
- content brief generation
- draft generation

The orchestrator delegates to:

- `TopicDiscoveryWorkflow`
- `ContentBriefWorkflow`
- `DraftGenerationWorkflow`

This keeps workflow logic out of controllers and centralizes retry-safe behavior.

## Admin Placeholders

The current backend already exposes the service surface that future admin UI screens can use.

### Topic Queue Placeholder

Topic Queue is backed by `content_topics` and admin routes for:

- listing topics
- creating manual topics
- approving topics
- rejecting topics
- marking topics used
- generating briefs from approved topics

Topic discovery can also be queued through the admin AI jobs endpoint:

- `POST /api/v1/admin/ai-jobs/topic-discovery`

### Content Brief Placeholder

Content Briefs are backed by `content_briefs` and admin routes for:

- listing briefs
- reading briefs
- updating briefs
- approving briefs
- generating draft jobs from approved briefs

### AI Jobs Placeholder

AI Jobs are backed by `ai_jobs` and admin routes for:

- listing jobs
- reading a job
- queueing topic discovery
- retrying failed jobs

This is the operational placeholder for future admin observability, retries, and audit workflows.

### Prompt Management Placeholder

Prompt templates are backed by `ai_prompt_templates` and `ai_prompt_template_versions` and already expose admin APIs for:

- list
- create
- read
- update
- add version
- activate version

The future admin UI can sit on top of this contract without changing the core backend flow.

## Queue And Execution Model

Long-running AI work uses the `ai` queue.

Expected execution model:

1. Admin or scheduler triggers a workflow.
2. The workflow creates an `ai_jobs` record.
3. The queue job is dispatched.
4. The agent runs through provider-agnostic AI client abstractions.
5. Generation steps, usage, and errors are recorded.
6. Domain records are updated only when business rules allow it.

## Retry And Safety Expectations

- Retries are explicit and tracked.
- Failed jobs can be retried from the AI jobs flow.
- Retry paths must not create duplicate topics, briefs, or posts.
- Existing domain state should be reused where possible instead of recreated.

## Provider And Prompt Boundaries

Provider details stay behind internal AI client abstractions.

Prompts are database-backed and versioned. Agent classes should not hardcode workflow prompts.

The current provider model is flexible, but the editorial workflow assumptions are fixed:

- draft-first
- human-reviewed
- auditable
- retryable

## What Is Not In Scope In This Phase

- autonomous publishing
- AI-generated images
- direct public frontend AI interaction
- replacing editorial approval with model decisions

## Summary

The AI content engine is a controlled editorial pipeline:

- discover topics
- approve topics
- generate briefs
- approve briefs
- generate drafts
- review and publish manually

That sequence is the current source of truth for future agents working in this repository.
