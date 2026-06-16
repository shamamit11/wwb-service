# Skill: Database

## Purpose

Use this skill for Laravel database work in `widewebblog/service`, including schema, models, repositories, and write consistency.

## Context To Load

- `.agent/ARCHITECTURE.md`
- `.agent/COMMANDS.md`
- `.agent/TESTING.md`
- `.agent/knowledge-base/architecture-decisions.md`

## Migration Rules

- use clear table and column names
- add indexes for foreign keys, slugs, status fields, and frequently filtered fields
- use soft deletes only where product behavior requires restore or history
- add timestamps unless there is a clear reason not to
- avoid destructive migration changes without explicit instruction

## Model Rules

- define relationships
- define casts
- keep model logic small
- prefer scopes for reusable filters

## Repository Query Rules

- keep query logic in repositories
- avoid duplicating query filters across services and controllers
- use pagination for list endpoints
- avoid N+1 queries by eager loading intentionally

## Transaction Rules

Use service-level transactions when multiple writes must succeed or fail together.
