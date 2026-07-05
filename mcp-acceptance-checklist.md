# EKA v2 MCP Acceptance Checklist

This document defines a reusable MCP acceptance test for EKA v2.

The goal is to verify that EKA can help MCP clients and engineers understand a repository from indexed source-backed knowledge, without depending on repository-owned agent scaffolding for repository-specific understanding.

## Purpose

This checklist separates four concerns that should be evaluated independently:

1. MCP transport and tool behavior
2. Focused structural question answering over repository source
3. High-level onboarding and repository understanding from canonical repository evidence
4. Proper handling of `.agent/**` as optional secondary context rather than a primary repository source

The intended acceptance standard is:

- MCP transport must work reliably
- Focused structural questions must already work well
- Broad onboarding-style repository understanding must come from canonical repository evidence
- `.agent/**` must not be the primary basis for repository-specific understanding
- Generic agent standards may be used only as secondary non-repository context

## Benchmark Repository

Primary benchmark repository for this test:

- `wwb-service`

This checklist may also be run against:

- `eka-service`
- other internal benchmark repositories with known structure and docs

## Canonical Evidence Preference

For onboarding-style prompts and repository overview prompts, EKA should prefer evidence from canonical repository artifacts such as:

- `README*`
- architecture or project overview documents
- API docs or OpenAPI exports
- route files
- controllers, services, modules, and folder structure
- repository knowledge artifacts derived from source

For these prompt types:

- `.agent/**` should not be the primary basis for repository purpose, modules, boundaries, architecture, or onboarding guidance
- generic organization-wide agent standards may appear only as supplemental context
- repository-specific knowledge stored under `.agent/**` should be down-ranked or filtered for overview-style prompts

## Test Modes

Run the checklist in both modes:

### Mode A: Normal Policy

Use normal retrieval behavior with `.agent/**` available.

Purpose:

- confirm that overall quality is strong in the default mode
- measure whether `.agent/**` dominates evidence selection too heavily
- verify that repository-specific understanding still comes primarily from canonical repository evidence

### Mode B: Repository-Specific Agent Knowledge Excluded

Run the same prompts with repository-specific `.agent/**` knowledge excluded from repository understanding.

Purpose:

- verify that repository overview and onboarding still work from canonical repository evidence
- allow only generic organization-wide agent standards if needed
- fail if `.agent/**` is used to explain repository purpose, modules, boundaries, architecture, or onboarding path

Mode B is the critical acceptance mode for the "no repository-specific agent scaffolding required" goal.

## Acceptance Areas

### 1. MCP Transport And Health

Objective:

- prove that MCP plumbing is functioning correctly

Checks:

- MCP server connects successfully
- repository can be selected or identified through MCP
- MCP tools respond without fallback to generic unsupported behavior
- trust/freshness/readiness information is surfaced
- response includes evidence, citations, or retrieval metadata when expected

Pass criteria:

- connection works consistently
- repository-targeted queries complete successfully
- evidence-bearing answers include grounded support

Fail examples:

- transport errors
- missing repository resolution
- answers returned without source-backed evidence where evidence is expected

### 2. Focused Structural Question Answering

Objective:

- verify that EKA can answer narrow repository-structure questions from source-backed evidence

Example prompt set:

- "What is the admin API boundary vs public API boundary?"
- "Where are routes registered?"
- "What modules handle indexing, MCP, and user-facing knowledge?"
- "What evidence do you have for repository freshness, trust, and coverage?"
- "Where is the repository ask / answer pipeline implemented?"

Pass criteria:

- answer is correct
- answer is specific rather than generic
- evidence comes from code, docs, or structured repository artifacts
- answer remains good in both Mode A and Mode B

Fail examples:

- hallucinates files or modules
- gives generic architecture descriptions with no repository grounding
- becomes materially worse when `.agent/**` is excluded

### 3. Repository Overview From Canonical Evidence

Objective:

- verify that EKA can explain the repository to a new engineer from canonical repository evidence rather than repository-owned agent scaffolding

Example prompt set:

- "Give me a repository overview using canonical repository evidence."
- "Summarize this repository for a new engineer on day one using canonical repository evidence."
- "What are the main modules and responsibilities in this repository? Do not rely on repository-specific `.agent/**` knowledge."
- "What are the first files a new engineer should read? Start from canonical docs and source."

Pass criteria:

- answer is coherent and practically useful
- answer identifies major modules, boundaries, and workflows
- answer cites canonical docs or code structure
- answer does not depend primarily on repository-specific `.agent/**`

Fail examples:

- says it cannot provide a good summary without repository-specific `.agent/**`
- uses `.agent/**` as the dominant basis for repository purpose, modules, or onboarding
- falls back to generic AI-style summary not supported by evidence

### 4. Evidence Quality And Provenance

Objective:

- verify that repository-overview answers are grounded in the right sources

Pass criteria:

- evidence is visible or inspectable
- evidence primarily comes from canonical repository artifacts
- `.agent/**` is at most supplemental
- repository-specific `.agent/**` knowledge is absent or non-dominant in Mode B
- the answer can be traced back to source or indexed repository facts

Suggested quality target for overview prompts:

- at least 80% of cited or referenced evidence should come from canonical repository sources

Fail examples:

- evidence is opaque
- overview claims are not traceable
- `.agent/**` dominates onboarding answers
- repository summary is mainly derived from `.agent/INDEX.md`
- "first files to read" starts with repo `.agent/` files instead of canonical docs or source

### 5. Retrieval Policy Behavior

Objective:

- verify that retrieval policy shapes evidence appropriately for the prompt type

Pass criteria:

- Mode A may use `.agent/**` when useful as supplemental context
- Mode A still derives repository-specific understanding primarily from canonical repository evidence
- Mode B shifts to canonical docs and code structure without collapsing
- structural QA quality remains stable across both modes
- onboarding-summary quality degrades only slightly, if at all, in Mode B

Fail examples:

- overview quality depends heavily on `.agent/**`
- excluding repository-specific `.agent/**` causes EKA to stop being useful for onboarding prompts
- canonical docs are ignored even when available
- architecture or module explanation depends on `.agent` module maps

## Standard Prompt Pack

Use the same prompt pack for each benchmark repository.

### Transport / Capability Prompts

- "Identify the repository and tell me what MCP knowledge tools are available."
- "What is the repository freshness, trust, or readiness state?"

### Structural QA Prompts

- "What is the admin API boundary vs public API boundary?"
- "Where are routes registered?"
- "What are the main modules responsible for indexing, MCP, and user-facing knowledge?"
- "Where would I look to understand repository question answering?"

### Onboarding / Overview Prompts

- "Give me a repository overview."
- "Summarize this repository for a new engineer on day one."
- "What are the main modules and responsibilities?"
- "What are the first files I should read?"

### Mode B Variants

Repeat the onboarding prompts with explicit repository-specific agent guidance restrictions:

- "Give me a repository overview using canonical repository evidence. Do not rely on repository-specific `.agent/**` knowledge."
- "Summarize this repository for a new engineer on day one using canonical repository evidence. Generic organization-wide agent standards may be supplemental only."
- "What are the main modules and responsibilities? Do not use repository-specific `.agent/**` as the basis."
- "What are the first files I should read? Start from canonical docs and source, not repo `.agent/` files."

## Scoring

Score each prompt on a 0-2 scale.

| Score | Meaning                                                         |
| ----- | --------------------------------------------------------------- |
| 0     | Wrong, vague, generic, or agent-dependent                       |
| 1     | Partially correct and useful, but incomplete or weakly grounded |
| 2     | Correct, grounded, specific, and practically useful             |

Suggested scoring dimensions:

- factual correctness
- specificity
- evidence quality
- independence from repository-specific `.agent/**` for Mode B prompts

## Release Gate

Suggested acceptance gate:

| Area                                                              | Gate         |
| ----------------------------------------------------------------- | ------------ |
| MCP transport and health                                          | Must pass    |
| Focused structural QA average                                     | `>= 1.5 / 2` |
| Repository overview with `.agent/**` allowed                      | `>= 1.5 / 2` |
| Repository overview with repository-specific `.agent/**` excluded | `>= 1.5 / 2` |
| Evidence quality for overview prompts                             | Must pass    |
| Mode B repository-specific `.agent/**` compliance                 | Must pass    |

Do not declare the repository-understanding goal complete unless the Mode B onboarding prompts meet the gate.

## Pass / Fail Evaluation Table

Use this table during each run.

| Area                      | Prompt | Mode | Expected Evidence Shape                                                              | Score (0-2) | Pass/Fail | Notes |
| ------------------------- | ------ | ---- | ------------------------------------------------------------------------------------ | ----------- | --------- | ----- |
| MCP transport and health  |        | A    | MCP tools, trust/freshness metadata, evidence-bearing response                       |             |           |       |
| Focused structural QA     |        | A    | code, routes, docs, structured repository facts                                      |             |           |       |
| Focused structural QA     |        | B    | code, routes, docs, structured repository facts                                      |             |           |       |
| Repository overview       |        | A    | docs, code structure, repository facts, optional supplemental `.agent/**`            |             |           |       |
| Repository overview       |        | B    | docs, code structure, repository facts, no repository-specific `.agent/**` basis     |             |           |       |
| Evidence quality          |        | B    | canonical repository artifacts dominate                                              |             |           |       |
| Retrieval policy behavior |        | A/B  | appropriate shift away from repository-specific `.agent/**` toward canonical sources |             |           |       |

## Results Template For `wwb-service`

Copy this section for each benchmark run.

### Run Metadata

| Field                   | Value         |
| ----------------------- | ------------- |
| Repository              | `wwb-service` |
| Date                    |               |
| Evaluator               |               |
| EKA environment         |               |
| MCP client used         |               |
| Index snapshot / commit |               |
| Mode                    | `A` or `B`    |

### Prompt Results

| Prompt                                                                              | Score (0-2) | Pass/Fail | Evidence Used | Notes |
| ----------------------------------------------------------------------------------- | ----------- | --------- | ------------- | ----- |
| Identify the repository and available MCP knowledge tools                           |             |           |               |       |
| What is the repository freshness, trust, or readiness state?                        |             |           |               |       |
| What is the admin API boundary vs public API boundary?                              |             |           |               |       |
| Where are routes registered?                                                        |             |           |               |       |
| What are the main modules responsible for indexing, MCP, and user-facing knowledge? |             |           |               |       |
| Where would I look to understand repository question answering?                     |             |           |               |       |
| Give me a repository overview                                                       |             |           |               |       |
| Summarize this repository for a new engineer on day one                             |             |           |               |       |
| What are the main modules and responsibilities?                                     |             |           |               |       |
| What are the first files I should read?                                             |             |           |               |       |

### Mode B Compliance Check

Use this section only for repository-specific `.agent/**`-restricted runs.

| Check                                                               | Result | Notes |
| ------------------------------------------------------------------- | ------ | ----- |
| repository-specific `.agent/**` excluded or down-ranked as intended |        |       |
| Answer remained useful without repository-specific `.agent/**`      |        |       |
| Evidence came primarily from canonical repository artifacts         |        |       |
| No hidden fallback to repository-owned agent scaffolding            |        |       |

### Summary Judgment

| Area                                                                    | Result | Notes |
| ----------------------------------------------------------------------- | ------ | ----- |
| MCP transport and health                                                |        |       |
| Focused structural QA                                                   |        |       |
| Repository overview with agent files allowed                            |        |       |
| Repository overview with repository-specific agent knowledge restricted |        |       |
| Evidence quality                                                        |        |       |
| Retrieval policy behavior                                               |        |       |
| Final acceptance outcome                                                |        |       |

### Final Conclusion Template

Use this wording shape for the final outcome:

- "For Codex-focused structural repository questions: pass / partial / fail."
- "For new-engineer onboarding with repository-specific `.agent/**` restricted: pass / partial / fail."
- "Primary gap: transport / retrieval policy / knowledge shaping / evidence quality / canonical coverage."
- "Recommended next action: adjust retrieval policy / improve canonical indexing / add stronger canonical onboarding artifacts / improve evidence controls."

## Notes For `eka-service`

When this checklist is run against `eka-service`, the expected canonical evidence sources include:

- `README.md`
- `PROJECT-OVERVIEW.md`
- `endpoints-required-fe.md`
- `required-endpoints-admin.md`
- `routes/api.php`
- module structure under `app/Modules`

Those artifacts are sufficient to support a source-grounded onboarding summary without relying on repository-specific `.agent/**`. That makes `eka-service` a useful control repository when validating retrieval policy behavior.
