## EKA MCP Usage

Use EKA MCP as the default repository context source before answering repository-specific questions or making implementation assumptions.

Default repository scope:

- `repositorySlug: wwb-service`

Expected behavior:

1. Resolve the repository context first.
    - Use the configured `repositorySlug` as the default scope.
    - If the repository is ambiguous, call `eka.identify_repository`.

2. Check trust and freshness before relying on repository understanding.
    - For onboarding, architecture, or broad repository questions, check repository trust/freshness first.
    - If trust is stale, conflicting, or insufficient, say so clearly and narrow the answer.

3. Prefer EKA for repository-grounded understanding.
    - Use EKA MCP for:
        - repository overview
        - trust/freshness/coverage
        - APIs, services, events, modules, evidence, and related repositories
        - repository question answering and file/fact lookup
    - Treat EKA evidence as the source of truth before using generic model assumptions.

4. Treat repository-owned `.agent/**` as supplemental only.
    - Do not use repository-specific `.agent/**` as the primary basis for repository purpose, architecture, module boundaries, or onboarding guidance.
    - Prefer canonical repository evidence such as:
        - `README*`
        - architecture or scope docs
        - route files
        - source structure
        - module/service/API facts derived from source

5. Fall back to direct code inspection when needed.
    - If EKA evidence is weak, incomplete, stale, or indirect, inspect the repository code directly.
    - Use EKA to guide where to look, not to replace source verification.

Prompting guidance:

- Keep questions repository-scoped.
- Example:
    - “For `wwb-service`, what are the main modules?”
    - “For `wwb-service`, where is the repository answer pipeline implemented?”
    - “For `wwb-service`, what is the current trust/freshness status?”
