# AI Job Lifecycle

## Purpose

This document describes how AI jobs move through the current service implementation.

## Why AI Jobs Exist

`ai_jobs` provide workflow-level tracking for long-running or reviewable AI operations.

They exist so the system can:

- track queued work
- record provider/model usage
- capture failures
- support retries
- expose operational status to future admin UI screens

## Current Workflow Types

The current lifecycle is used for:

- topic discovery
- content brief generation
- blog draft generation

## Current Statuses

The current backend uses these job statuses:

- `pending`
- `queued`
- `processing`
- `completed`
- `failed`
- `cancelled`
- `reviewed`

Not every status is exercised by every workflow yet, but these are the supported states in the model.

## Lifecycle By Workflow

### Topic Discovery

1. Admin API, scheduler, or console command requests topic discovery.
2. `TopicDiscoveryWorkflow::dispatch()` creates an `ai_jobs` record with `queued` status.
3. `DiscoverContentTopicsJob` is dispatched onto the `ai` queue.
4. The queued workflow executes the agent and persistence flow.
5. Saved topics and skipped duplicates are recorded in job output metadata.

### Content Brief Generation

1. An approved topic is selected.
2. `ContentBriefWorkflow::generate()` checks for an existing brief first.
3. If no brief exists, an AI job is created for the workflow.
4. The brief generation service runs and persists the resulting brief.

### Draft Generation

1. An approved content brief is selected.
2. `DraftGenerationWorkflow::queue()` creates an `ai_jobs` record with `queued` status.
3. `GenerateBlogDraftJob` is dispatched to the `ai` queue.
4. The queued workflow either reuses an existing post or generates a new draft safely.

## AI Generation Steps

`ai_generation_steps` sit under the parent job and record per-agent execution details such as:

- agent name
- status
- input snapshot
- output snapshot
- usage payload
- error message

These records are important because a single workflow can involve more than one meaningful generation step over time.

## Job Inputs And Outputs

The job record stores:

- workflow type
- entity type and entity ID
- provider and model when available
- input payload
- output payload
- usage payload
- attempts
- retry linkage
- timestamps

This is the stable operational record that future agents and admin UI should expect.

## Retry Behavior

Retries are explicit.

The current retry path is orchestrator-driven and workflow-aware:

- topic discovery retries rebuild discovery input and queue a new job
- content brief retries create a new queued brief job
- blog writer retries queue a new draft-generation job from the source brief

Retry safety expectations:

- do not duplicate topics unnecessarily
- do not generate multiple briefs for the same topic when one already exists
- do not create duplicate posts when an existing brief-linked draft already exists

## Admin Placeholder

The current AI Jobs admin placeholder is the backend API for:

- listing jobs
- reading a job
- queueing topic discovery
- retrying failed jobs

This is the operational entry point that future admin UI work is expected to consume.

## Human Approval Boundary

AI jobs track generation state, not editorial approval state.

That distinction matters:

- an AI job may complete successfully
- the resulting topic, brief, or post draft may still require human review
- successful AI completion never implies publish permission

## Manual Images In This Phase

Image generation is outside the current AI job lifecycle.

The MVP allows AI to suggest:

- image ideas
- placement notes
- alt text suggestions

But no image-generation job should be assumed in this phase.

## Summary

The AI job lifecycle is the operational spine of the AI content engine. It makes workflows:

- observable
- retryable
- auditable
- safe to expose through future admin tooling
