# Content Lifecycle

## Purpose

Use this file for stable content-state rules across posts, topics, SEO, knowledge links, and future AI workflows.

## Core Lifecycle

1. Topic identified
2. Topic approved or rejected
3. Blueprint prepared
4. Draft created
5. Review and revision
6. SEO completion
7. Scheduled or published
8. Refreshed, archived, or retired

## Draft-First Rule

- AI may suggest or draft content.
- AI must never publish directly.
- Human approval is required before publish-state transitions.

## Source Of Truth

- Structured content blocks are the primary article source.
- Templates shape rendering, not authorship ownership.
- SEO metadata is attached to the post lifecycle, not treated as a disconnected addon.

## Review Expectations

- Technical accuracy beats publishing speed.
- Knowledge-base links should support originality, evidence, and internal consistency.
- Refresh workflows should preserve canonical URLs unless there is a strong SEO reason to change them.

## State Transition Guidance

- Keep publish-state transitions explicit and auditable.
- Separate editorial state from background processing state.
- Use jobs for generation and analysis states, not for publish authorization.

## Future Expansion

- Multi-author and editorial workflow should extend this lifecycle, not replace it.
- Topic queue, blueprint, and AI jobs should all map back to the same post lifecycle model.
