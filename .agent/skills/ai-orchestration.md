# Skill: AI Orchestration

## Purpose

Use this skill for provider abstraction, prompt orchestration, AI jobs, generation-step tracking, topic discovery, content brief generation, draft generation, and token or cost tracking.

## Context To Load

- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/ai-content.md`
- `WB_SERVICE_AI_AGENTS_TASKS.md` when sequencing or scope matters
- `docs/AI_CONTENT_ENGINE.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/queue-scheduler.md`

## Use This When

- adding AI providers or provider abstractions
- building topic discovery, content brief, or draft workflows
- generating drafts, FAQs, tags, or SEO metadata
- tracking AI jobs, retries, tokens, and cost

## Non-Negotiable Rules

- AI must not publish directly
- generated content remains draft or review state until admin approval
- provider-specific code stays behind abstractions
- prompts must be database-backed and versioned
- prompts, outputs, usage, and job status should be inspectable
- each workflow must create an `ai_jobs` record
- each agent execution must create an `ai_generation_steps` record
- long-running AI jobs use the `ai` queue
- retries must not duplicate topics, briefs, or posts

## Orchestration Checklist

- define the user-visible workflow state
- separate provider client, prompt builder, and orchestration service
- dispatch heavy work to queues
- separate editorial approval state from background execution state
- track usage and cost per meaningful unit of work
- distinguish retryable provider failures from permanent failures
- ensure only approved topics can create briefs
- ensure only approved briefs can create draft posts
