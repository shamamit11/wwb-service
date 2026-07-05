# Skill: API Contracts

## Purpose

Use this skill for endpoint shape design, request and response contract updates, resource formatting, pagination, filtering, and contract-safe API evolution.

## Context To Load

- `.agent/knowledge-base/api-standards.md`
- `.agent/knowledge-base/module-map.md`
- `.agent/ARCHITECTURE.md`
- `.agent/skills/laravel-api.md`

## Use This When

- adding a new API endpoint
- changing response fields
- changing filters, sorts, or pagination
- aligning code with `docs/OPENAPI_SPEC.md`
- evaluating whether admin or frontend consumers may break

## Contract Checklist

- route and HTTP method fit the use case
- request fields are validated
- DTO shape matches validated input
- API Resource defines output shape
- pagination format is consistent
- filter and sort parameters are explicit
- error behavior is predictable
- docs are updated if the contract changed

## Contract Safety Rules

- prefer additive changes over breaking changes
- keep naming business-oriented, not persistence-oriented
- avoid leaking internal provider details or raw storage paths
- record cross-app impact when a contract changes
