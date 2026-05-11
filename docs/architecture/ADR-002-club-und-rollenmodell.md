# ADR-002: Club- und Rollenmodell

## Status

Accepted

## Context

Mehrere Benutzer arbeiten pro Verein mit abgestuften Rechten.

## Decision

Wir modellieren Vereinsrollen ueber die Pivot-Tabelle `club_user` mit Rollen `owner`, `admin`, `coach`, `member`.

## Consequences

- Rechte koennen zentral ueber Policies/Gates umgesetzt werden.
- Vereinsbezogene Freigaben sind sauber nachvollziehbar.
