# AGENTS.md

Shared operating contract for coding agents working in this repository, including Codex, Claude, and GitHub Copilot.

## Purpose

Use this file for agent behavior, workflow rules, and repository guardrails.

Do not treat this file as the primary source of repository architecture or onboarding knowledge. Repository understanding should come from canonical docs and source code.

For local EKA-aligned agent workflow details, also read:

- `.agent/eka.md`
- `.agent/read-order.md`
- `.agent/coding-rules.md`

## Canonical Repository Sources

Start repository understanding from these sources:

- `README.md`
- `docs/ARCHITECTURE.md`
- `docs/MVP_SCOPE.md`
- `app/Modules/README.md`

Prefer those files over repo-owned agent scaffolding when explaining purpose, modules, boundaries, onboarding path, or architecture.

## Working Rules

- Read only the minimum code and documentation needed for the task.
- Prefer source-backed conclusions over assumptions.
- Keep changes scoped to the requested feature or bug.
- Do not rewrite unrelated code just to match personal style.
- Preserve existing patterns unless there is a clear reason to change them.

## Implementation Pattern

Default backend flow in this repository:

`Controller -> FormRequest -> DTO -> Service/Action -> Repository -> Model/Client -> API Resource`

Follow established module patterns if a local area already uses a narrower convention.

## Testing Expectations

- Run the smallest relevant test set for the change when feasible.
- If you cannot run tests, say so explicitly.
- Prefer adding or updating tests when behavior changes.

## API And Contract Discipline

- Treat API contracts as deliberate surfaces.
- If changing request or response payloads, check for impact on sibling clients.
- Call out cross-repo contract changes clearly.

## Safety And Boundaries

- AI or automation must not be treated as editorial approval.
- Do not assume publishing can be automated unless the code and product rules explicitly allow it.
- Avoid destructive operations unless explicitly requested.

## Cross-Repo Context

Sibling applications may consume this service, but they are not part of this runtime.

When relevant, check cross-repo implications for:

- API changes
- auth changes
- payload changes
- workflow changes that affect admin or frontend clients

## What Not To Put Here

Do not use this file as the only place for:

- architecture overview
- module map
- onboarding summary
- product scope
- repository-specific deep knowledge

That material belongs in canonical repository docs so humans and MCP systems can derive it from normal project sources.
