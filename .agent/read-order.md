# Read Order

Read these in order before changing code:

1. `AGENTS.md` if it contains repository-wide instructions.
2. `.agent/README.md`
3. EKA MCP repository context when the task is about indexed code, architecture, endpoints, or cross-repository behavior.
4. `.agent/coding-rules.md`
5. `app/Modules/README.md`
6. The affected route, controller, module service, repository, or AI workflow files.
7. Tests covering the affected behavior in `tests/Feature` or `tests/Unit`.

When a local module convention conflicts with a generic template rule, follow the local module convention.
When current workspace state may differ from indexed knowledge, verify with local files and call out the distinction.
