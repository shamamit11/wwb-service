# Skill: AI Content Engine

## Purpose

Use this skill for service-side content generation workflows, prompt templates, provider client abstraction, and review-before-publish behavior.

## Use When

- designing topic discovery flows
- generating draft blog content
- creating AI-assisted SEO suggestions
- planning future image generation workflows

## Context To Load

- `.agent/PROJECT-CONTEXT.md`
- `.agent/knowledge-base/ai-content.md`
- `.agent/knowledge-base/product.md`
- `.agent/skills/seo.md` when metadata is involved

## Working Rules

- Every AI output begins as draft content.
- Human review is mandatory before publication.
- Prompting, generation, moderation, and approval should be separable stages.
- Keep provider integrations behind service and client abstractions.
- Record model/provider assumptions in the task file, not memory, unless they become a stable platform choice.

## Design Bias

Favor pipelines that are:
- auditable
- retryable
- queue-friendly
- easy to override manually in admin
