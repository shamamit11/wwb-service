# Doc Map

## Purpose

Use this file to choose the smallest project document set for strategy-heavy tasks without reopening the full planning corpus.

## Document Routing

- Product direction: `docs/PRODUCT_VISION.md`
- MVP boundaries: `docs/MVP_SCOPE.md`
- service architecture: `docs/ARCHITECTURE.md`
- persistence design: `docs/DATABASE_DESIGN.md`
- editorial strategy: `docs/CONTENT_STRATEGY.md`
- SEO policy: `docs/SEO_STRATEGY.md`
- delivery sequence: `docs/ROADMAP.md`
- full work backlog: `docs/TASKS.md`
- service-only backlog: `docs/SERVICE_TASKS.md`

## Implementation Specs

- API contracts: `docs/OPENAPI_SPEC.md`
- template system: `docs/TEMPLATE_ENGINE.md`
- media system: `docs/MEDIA_SERVICE.md`
- knowledge base: `docs/KNOWLEDGE_BASE.md`
- AI engine: `docs/AI_CONTENT_ENGINE.md`

## Loading Rule

- Load a project document only when the task is strategic, ambiguous, or requires contract alignment.
- Prefer `.agent/knowledge-base/*` files for stable distilled rules.
- Prefer `.agent/skills/*` files for implementation behavior.
