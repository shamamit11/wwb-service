# Claude Instructions

## Role

Use Claude as a focused implementation and reasoning agent with strict context discipline.

## Claude-Specific Guidance

- Do not preload large file sets.
- Use `.agent/INDEX.md` to choose only the needed docs.
- Keep plans aligned to the task file instead of long conversational scaffolding.
- When context is missing, inspect the narrowest possible repository slice first.
- Write durable discoveries to `.agent/MEMORY.md` only after confirming they are stable.
