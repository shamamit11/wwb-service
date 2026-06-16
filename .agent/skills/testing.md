# Skill: Testing

## Purpose

Use this skill for Laravel service testing in `widewebblog/service`.

## Context To Load

- `.agent/TESTING.md`
- `.agent/COMMANDS.md`
- the task-specific service skill for the changed area

## Test Types

- feature tests for API endpoints
- unit tests for services or actions where logic is isolated
- integration tests for repository behavior when valuable
- fake external clients for AI, image, or storage integrations

## Minimum Testing Expectations

For new API endpoints:

- success case
- validation failure
- not found case where relevant
- authorization failure where relevant
- state change assertion for write operations

## Test Rules

- prefer narrow tests first
- do not rely on real external AI, image, or storage services
- use fakes or mocks according to the existing project pattern
- keep tests deterministic
- run the smallest meaningful test command first
