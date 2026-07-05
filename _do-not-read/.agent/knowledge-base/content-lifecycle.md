# Content Lifecycle

## Purpose

Use this file for stable lifecycle rules across topics, posts, SEO, knowledge links, and AI workflows.

## Core Lifecycle

1. Topic identified
2. Topic scored
3. Topic auto-pruned or auto-queued
4. Draft created
5. Review and revision
6. SEO completion
7. Scheduled or published
8. Refreshed, archived, or retired

## Draft-First Rule

- AI may suggest and draft content.
- AI must never publish directly.
- Human approval is required before publish-state transitions.

## Source Of Truth

- `posts` owns the canonical article body.
- `full_article_markdown` and optional `full_article_html` are the article content source of truth.
- SEO metadata attaches to the post lifecycle rather than replacing it.

## Review Expectations

- Technical accuracy beats publishing speed.
- Knowledge Base context should improve originality, evidence, and internal consistency.
- Refresh workflows should preserve canonical URLs unless there is a strong SEO reason to change them.

## State Transition Guidance

- Keep publish-state transitions explicit and auditable.
- Separate editorial state from background processing state.
- Use jobs for generation and analysis states, not for publish authorization.
- Topics below the score threshold are disposable and should not require admin review.
- Topics at or above the score threshold may queue draft generation automatically.
- Retryable background work must not duplicate domain records.

## Future Expansion

- Multi-author workflow should extend this lifecycle, not replace it.
- Topic queue and AI jobs should still map back to the same post lifecycle model.
