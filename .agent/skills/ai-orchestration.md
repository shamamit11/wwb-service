# Skill: AI Orchestration

## Purpose

Use this skill for provider abstraction, prompt orchestration, AI jobs, topic discovery, draft generation, and token or cost tracking.

## Context To Load

- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/ai-content.md`
- `docs/AI_CONTENT_ENGINE.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/queue-scheduler.md`

## Use This When

- adding AI providers or provider abstractions
- building topic discovery or blueprint workflows
- generating drafts, FAQs, tags, or SEO metadata
- tracking AI jobs, retries, tokens, and cost

## Non-Negotiable Rules

- AI must not publish directly
- generated content remains draft or review state until admin approval
- provider-specific code stays behind abstractions
- prompts, outputs, and job status should be inspectable

## Orchestration Checklist

- define the user-visible workflow state
- separate provider client, prompt builder, and orchestration service
- dispatch heavy work to queues
- track usage and cost per meaningful unit of work
- distinguish retryable provider failures from permanent failures
