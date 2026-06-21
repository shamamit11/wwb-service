# Skill: Activity Log And Audit

## Purpose

Use this skill for features that create auditable admin actions or require Spatie Activitylog integration.

## Context To Load

- `.agent/knowledge-base/activitylog-policy.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/laravel-api.md`

## Use This When

- adding create, update, delete, publish, or approval flows
- changing settings or prompt configuration
- adding AI workflow approval steps
- deciding what should or should not be audited

## Audit Checklist

- identify the business event worth logging
- log actor, subject, event name, and meaningful changed fields
- avoid secrets and noisy payloads
- ensure descriptions are readable to humans
- add tests if audit behavior is critical to the workflow

## Guardrails

- do not log every trivial mutation
- do not log raw credentials or provider tokens
- treat audit logs as product behavior, not an afterthought
