# Database Design

## Purpose

This document describes the current fresh-start database baseline for Wide Web Blog.

It reflects the simplified editorial model now implemented in the service:

- article-first posts
- scored topic queue
- direct topic-to-draft generation
- versioned prompt templates for the two standard prompt families
- no templates, content briefs, or post blocks

## Core Principles

- `posts` is the source of truth for article content.
- `content_topics` is the source of truth for AI-suggested topic intake.
- `ai_jobs` and `ai_generation_steps` track execution state, not editorial publish authority.
- `seo_metadata` augments posts and other entities without owning content.
- `media` stores metadata for files persisted in Cloudflare R2.

## Core Tables

### `posts`

Canonical post fields include:

- `author_user_id`
- `category_id`
- `featured_media_id`
- `title`
- `slug`
- `short_description`
- `description`
- `full_article_html`
- `full_article_delta`
- `faq`
- `status`
- `visibility`
- `published_at`
- `meta`

Important notes:

- `template_id` does not exist
- block composition tables do not exist
- article content lives directly on the post record

### `content_topics`

The topic queue stores scored discovery output and manual topic entries.

Important fields include:

- `title`
- `slug`
- `cluster`
- `primary_keyword`
- `secondary_keywords`
- `search_intent`
- `priority_score`
- `score_breakdown`
- `difficulty_note`
- `source`
- `status`
- `notes`
- `approved_at`

Current business rules:

- topics below `90` are pruned automatically
- topics above `90` may auto-queue draft generation

### `ai_prompt_templates`

The main flow uses exactly two managed prompt families:

- `topic_standard`
- `blog_standard`

Template versions are stored in `ai_prompt_template_versions`.

### `ai_jobs`

Tracks workflow-level AI execution such as:

- topic discovery
- blog draft generation
- helper suggestion flows

### `ai_generation_steps`

Tracks per-agent execution details inside a job.

### `seo_metadata`

Polymorphic SEO metadata attached to posts and other supported entities.

### `media`

Stores object identity, metadata, attribution, and usage-linked references for assets stored in R2.

### `knowledge_base_entries`

Stores grounding material used by topic discovery and blog generation.

## Removed Tables

These are not part of the current baseline:

- `content_briefs`
- `templates`
- `template_blocks`
- `post_blocks`
- `agent_conversations`
- `agent_conversation_messages`

## Queue And Cache Defaults

Default local development assumes:

- database-backed queue
- database-backed cache
- no Redis requirement

## Migration Baseline

The project is intended to bootstrap cleanly with:

```bash
php artisan migrate:fresh --seed
```
