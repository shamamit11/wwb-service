# Skill: AI Content Engine

## Purpose

Use this skill for service-side AI content workflows, including topic discovery, scored topic routing, draft generation, prompt template management, provider abstraction, and review-before-publish behavior.

## Use When

- designing topic discovery flows
- generating draft blog content
- creating AI-assisted SEO suggestions
- planning prompt template or AI job tracking behavior
- planning future image generation workflows

## Context To Load

- `.agent/PROJECT-CONTEXT.md`
- `.agent/ARCHITECTURE.md`
- `.agent/knowledge-base/content-lifecycle.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/knowledge-base/queue-conventions.md`
- `.agent/skills/seo.md` when metadata is involved

## Working Rules

- Every AI output begins as draft content.
- Human review is mandatory before publication.
- The current MVP sequence is topic discovery -> score/prune -> auto-queue draft generation for scores above `90` -> manual review/publish.
- Prompting, generation, moderation, and approval should be separable stages.
- Keep provider integrations behind service and client abstractions.
- Keep prompts database-backed and versioned rather than hardcoded in agents.
- Track workflow-level jobs and per-agent generation steps.
- Allow topic discovery only inside approved content clusters.
- Treat the main editable prompt families as `topic_standard` and `blog_standard`.
- Images are manual in this phase; AI may only suggest image ideas and alt text.
- Use the `ai` queue for long-running AI jobs.

## Design Bias

Favor pipelines that are:

- auditable
- retryable
- queue-friendly
- easy to override manually in admin
- based on structured outputs rather than raw HTML-first content
