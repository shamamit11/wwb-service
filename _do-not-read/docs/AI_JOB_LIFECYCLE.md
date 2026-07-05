# AI Job Lifecycle

## Purpose

This document describes how AI jobs move through the simplified Wide Web Blog service workflow.

## Why AI Jobs Exist

`ai_jobs` provide operational tracking for long-running or reviewable AI work.

They exist so the system can:

- track queued work
- record provider and model usage
- capture failures
- support retries
- expose workflow state to admin tooling

## Active Workflow Types

The current lifecycle is centered on:

- topic discovery and scoring
- article draft generation

Legacy content brief workflows are no longer part of the active system.

## Supported Statuses

The backend supports these job states:

- `pending`
- `queued`
- `processing`
- `completed`
- `failed`
- `cancelled`
- `reviewed`

Not every workflow uses every state, but these remain the shared operational statuses.

## Topic Discovery Lifecycle

1. A scheduler, command, or admin action requests topic discovery.
2. A workflow service creates an `ai_jobs` record for the discovery run.
3. A queued job runs the Topic Agent using category and knowledge-base context.
4. The workflow stores scored topics and records duplicates or skips in job metadata.
5. Automatic routing then evaluates the score:
   - scores below `70` are deleted by automated cleanup
   - scores from `70` through `84.99` remain in Topic Queue for editorial review
   - scores at or above `85` queue draft generation automatically

## Draft Generation Lifecycle

1. A qualified topic is selected by automation.
2. A draft-generation workflow creates an `ai_jobs` record.
3. A queued job runs the Blog Agent using the standard blog prompt.
4. The workflow creates or updates a draft post as a single article record.
5. The resulting post remains draft-only for admin review.

## AI Generation Steps

`ai_generation_steps` sit under a parent `ai_jobs` record and capture step-level execution detail such as:

- agent name
- status
- input snapshot
- output snapshot
- usage payload
- error message

This allows one workflow to remain inspectable even when it includes multiple internal agent actions.

## Job Inputs And Outputs

Job records store workflow-facing metadata such as:

- workflow type
- entity type and entity ID
- provider and model when available
- input payload
- output payload
- usage payload
- attempts
- retry linkage
- timestamps

This is the canonical operational record for the AI pipeline.

## Retry Behavior

Retries are explicit and workflow-aware.

Current expectations:

- topic discovery retries should not create unnecessary duplicate topics
- draft generation retries should avoid creating duplicate posts for the same source topic when a reusable draft already exists

Retries should preserve observability by creating a new job record rather than mutating the history away.

## Approval Boundary

AI jobs track generation state, not editorial approval state.

That distinction is mandatory:

- an AI job can complete successfully
- the resulting topic may still be rejected by score-based automation
- the resulting post draft may still require editing
- no completed AI job can publish content on its own

## Image Scope

Image generation is outside the current AI job lifecycle.

AI may support article creation with:

- featured image suggestions
- placement guidance
- alt text suggestions

But no image-generation workflow is required in the current baseline.

## Summary

The simplified AI job lifecycle exists to make the topic-to-article pipeline observable, retryable, and safe:

- discover topics
- score and route them automatically
- generate article drafts
- keep human approval at the publish boundary
