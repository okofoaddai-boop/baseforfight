# ADR-001: Modularisierungsstrategie

## Status

Accepted

## Context

Die Anwendung soll langfristig in klar getrennte Fachbereiche aufgeteilt werden.

## Decision

Wir nutzen eine modulare Ordnerstruktur unter `app/Modules/*` als Startpunkt.

## Consequences

- Domainen koennen getrennt entwickelt werden.
- Spaeterer Wechsel auf echte Package-Module bleibt moeglich.
