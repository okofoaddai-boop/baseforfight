# ADR-003: API-Versionierung und Auth

## Status

Accepted

## Context

Drittprojekte und Mobile Clients brauchen stabile Schnittstellen.

## Decision

Die API startet unter `/api/v1` und nutzt Laravel Sanctum fuer Token-Auth.

## Consequences

- Breaking Changes koennen ueber neue API-Versionen ausgeliefert werden.
- Zugriff kann ueber Token Scopes granular gesteuert werden.
