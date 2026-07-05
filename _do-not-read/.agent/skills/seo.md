# Skill: SEO

## Purpose

Use this skill for service-side slug generation, SEO fields, canonical URL data, structured data fields, and sitemap-supporting metadata.

## Use When

- optimizing public content discoverability
- generating or refining metadata, slugs, titles, excerpts, or schema
- connecting AI-assisted SEO workflows to editorial review

## Context To Load

- `.agent/knowledge-base/seo.md`
- `.agent/knowledge-base/product.md`
- `.agent/skills/livewire-frontend.md` for frontend work
- `.agent/skills/ai-content-engine.md` for AI-assisted metadata work

## Working Rules

- Keep SEO output editable by admins.
- Treat AI-generated SEO suggestions as draft input, not final publish state.
- Prefer deterministic fallbacks when AI metadata is missing.
- Keep SEO data shaped for frontend consumption without coupling to frontend implementation.
- Record any canonical URL, structured data, or sitemap implications.
