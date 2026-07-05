# Skill: Service Testing Matrix

## Purpose

Use this skill to choose the smallest effective validation set for service-repository work.

## Context To Load

- `.agent/TESTING.md`
- `.agent/COMMANDS.md`
- `.agent/skills/testing.md`
- the primary domain skill for the changed area

## Validation Matrix

- API contract change: feature tests plus docs verification
- repository or query change: focused integration or feature coverage
- DTO or service rule change: unit or narrow feature coverage
- queue or scheduler change: dispatch tests and targeted behavior tests
- media or provider integration change: fake or mock external clients
- audit logging change: assert activity entries where business-critical

## Selection Rules

- start with the narrowest meaningful test
- expand only when the change crosses module boundaries
- prefer deterministic fakes over real services
- validate docs and resources when contract fields change
