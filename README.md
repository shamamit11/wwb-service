# Wide Web Blog Service

Laravel backend for Wide Web Blog.

## Current Backend Direction

This service now uses a simplified editorial model:

- topic queue backed by `content_topics`
- knowledge-base-grounded topic discovery
- score-based topic filtering
- automatic draft generation for high-scoring topics
- article-first posts
- manual admin review before publish
- versioned prompt templates for the two standard prompt families

Removed from the active architecture:

- content briefs
- templates
- template blocks
- post blocks
- rewrite workflows
- Redis as the default local cache/queue requirement

## AI Flow

1. `TopicDiscoveryAgent` proposes scored topics using approved clusters and knowledge base context.
2. topics below `90` are pruned automatically.
3. topics above `90` automatically queue draft generation.
4. `BlogWriterAgent` generates one full article draft.
5. admin reviews, edits, and publishes manually.

AI never publishes directly.

## Canonical Post Shape

Posts are article-first and support:

- `title`
- `short_description`
- `description`
- `full_article_markdown`
- `full_article_html`
- `featured_media_id`
- `faq`
- SEO metadata
- `meta` for workflow and AI provenance

This shape is intended to work with a future Quill-based admin editor.

## Prompt Management

Prompt text is versioned in:

- `ai_prompt_templates`
- `ai_prompt_template_versions`

The editable prompt families for the main flow are:

- `topic_standard`
- `blog_standard`

`site_settings` is not used for prompt ownership.

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

Default local assumptions:

- database-backed queue
- database-backed cache
- no Redis required

## Useful Commands

```bash
php artisan migrate:fresh --seed
php artisan test
php artisan queue:work --queue=ai,default
php artisan schedule:work
php artisan topics:prune-low-score
```

## Documentation

Primary backend docs live in:

- `.agent/`
- `docs/AI_CONTENT_ENGINE.md`
- `docs/PROMPT_MANAGEMENT.md`
- `docs/DATABASE_DESIGN.md`
