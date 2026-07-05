# MCP Follow-Up Work

This document captures the post-acceptance follow-up work after the `wwb-service` benchmark passed the revised MCP acceptance checklist.

The major acceptance blockers are resolved:

- canonical repository evidence is now preferred for onboarding and overview prompts
- repository-specific `.agent/**` no longer dominates repository understanding
- exclusion-mode behavior works for the prompt families that previously failed
- MCP/tool-capability answers now come from MCP/server/application evidence rather than repo-owned agent scaffolding

What remains is quality improvement work, not acceptance-blocking product correction.

## Current Status

Current benchmark conclusion:

- `wwb-service` passes the revised checklist closely enough to count as a pass
- remaining issues are narrow prompt-quality weaknesses
- the weakest areas are focused structural prompts rather than onboarding prompts

## Remaining Quality Gaps

### 1. Structural Module Vocabulary

Weak prompt:

- "What are the main modules responsible for indexing, MCP, and user-facing knowledge?"

Why it is weaker:

- some repositories do not expose these concepts in explicit module vocabulary
- the current answer path still depends on inferred structure more than explicit repository facts

Suggested work:

- add stronger derived module facts during indexing
- persist repository role groupings such as `indexing`, `mcp`, `user-facing knowledge`, `admin`, `cross-repository`
- expose those role groupings as first-class knowledge artifacts or summary facts

### 2. Repository Question-Answering Entrypoints

Weak prompt:

- "Where would I look to understand repository question answering?"

Why it is weaker:

- retrieval is now correct enough, but the answer can still feel indirect
- the system does not yet promote a compact, canonical list of implementation entrypoints for this concept

Suggested work:

- define a small set of preferred answer-pipeline entrypoints during retrieval
- prefer files such as:
  - repository stateless answer service
  - conversation answer builder
  - evidence planner
  - context builder
  - trust evaluator
- consider adding a derived "answer pipeline" fact model during indexing

### 3. Prompt-Family-Specific Retrieval Profiles

What improved:

- overview/onboarding prompts now behave correctly
- MCP capability prompts now behave correctly

What remains:

- structural prompt families still need sharper retrieval shaping

Suggested work:

- split structural prompts into narrower profiles such as:
  - module responsibility
  - answer pipeline internals
  - MCP capability internals
  - trust/status
- tune evidence weighting per profile instead of relying on generic workflow routing

### 4. Canonical Summary Artifacts

Why this still matters:

- passing `wwb-service` does not mean every repository has enough canonical onboarding material
- retrieval quality is still constrained by source quality

Suggested work:

- define recommended canonical repository artifacts for strong MCP onboarding quality:
  - `README*`
  - architecture overview
  - MVP or scope document
  - module or package README files
  - route or API docs
- make missing-canonical-artifact diagnostics visible in readiness or coverage reporting

## Next Benchmark Stage

Do not stop at `wwb-service`.

Use the next benchmark stage to avoid overfitting retrieval policy to one repository shape.

Recommended benchmark mix:

- one repository with strong docs and weak `.agent/**`
- one repository with strong `.agent/**` and weaker canonical docs
- one repository with deeper module structure and fewer top-level docs
- `eka-service` as a control repository

Recommended benchmark questions:

- onboarding overview
- first files to read
- main modules and responsibilities
- answer-pipeline entrypoints
- MCP capability entrypoints
- trust/freshness/readiness

## Suggested Implementation Order

1. Improve derived module-role facts for indexing, MCP, and user-facing knowledge.
2. Add a more explicit answer-pipeline retrieval profile.
3. Add diagnostics for missing canonical onboarding artifacts.
4. Run the revised checklist on at least two non-`wwb-service` repositories.

## Exit Criteria For This Follow-Up

This follow-up can be considered complete when:

- structural prompt quality improves for module-responsibility questions
- answer-pipeline prompts return more direct entrypoints consistently
- at least one additional benchmark repository passes the revised checklist
