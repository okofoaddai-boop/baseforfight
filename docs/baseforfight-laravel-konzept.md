# BaseForFight 2.0 - Laravel Konzept

## 1. Zielbild

Das bestehende Legacy-System wird als neues, zukunftssicheres Laravel-Projekt aufgebaut.
Die Kernfunktionen bleiben erhalten:

- Benutzerverwaltung
- Kaempferprofile
- Veranstaltungen erstellen, verwalten und absagen
- Einschreibungen von Kaempfern
- Uebersichten, Exporte und Benachrichtigungen

Erweiterungen im neuen System:

- Vereinsbasierte Zusammenarbeit (mehrere Benutzer pro Verein mit gemeinsamen Rechten)
- Modularer Aufbau fuer spaetere Fachmodule
- API-First Faehigkeit fuer Drittprojekte, Mobile Apps und Integrationen
- GitHub-faehige Projektstruktur mit CI/CD-Basis

---

## 2. Architekturprinzipien

### 2.1 Technologiestack

- Laravel 12 (oder aktuelle LTS-Version bei Start)
- PHP 8.3+
- MySQL/MariaDB
- Redis (Queue, Cache, Rate Limiting)
- Laravel Horizon (Queue Monitoring)
- Laravel Sanctum (Token-Authentifizierung fuer API)
- PHPUnit/Pest fuer Tests

### 2.2 Leitlinien

- Domain-getrennte Module statt monolithischer Seitenlogik
- Klare Trennung von Domain, Application, Infrastructure, Interface (Controller/API)
- Service- und Action-Klassen statt Fachlogik in Controllern
- Events/Listener fuer entkoppelte Prozesse (Mailing, Logging, Benachrichtigung)
- API-Versionierung von Beginn an (v1)

---

## 3. Zielstruktur des Projekts

## 3.1 Verzeichnisstruktur (konzeptionell)

```text
baseforfight/
  app/
    Modules/
      Auth/
      Clubs/
      Fighters/
      Events/
      Registrations/
      Messaging/
      Billing/
      Admin/
      Shared/
  routes/
    web.php
    api.php
  database/
    migrations/
    seeders/
    factories/
  tests/
    Feature/
    Unit/
  docs/
    architecture/
    api/
  .github/
    workflows/
  README.md
```

Hinweis:
Je nach Teamentscheidung kann spaeter auf echte Package-Module gewechselt werden (zum Beispiel mit nwidart/laravel-modules oder als interne Composer-Packages).

---

## 4. Rollen- und Vereinsmodell (Hauptaenderung)

## 4.1 Grundidee

Ein Benutzer gehoert nicht nur sich selbst, sondern kann Mitglied in einem oder mehreren Vereinen sein.
Kaempferprofile koennen einem Verein gehoeren und damit von berechtigten Vereinsmitgliedern eingesehen und bearbeitet werden.

## 4.2 Kernentitaeten

- users
- clubs
- club_user (Pivot)
- fighters
- fighter_club (optional, falls Kaempfer mehreren Vereinen zugeordnet sein sollen)
- events
- event_registrations

## 4.3 Rollen im Verein

Vorschlag fuer club_user.role:

- owner (Vereinsinhaber)
- admin (darf Mitglieder/Kaempfer/Eventdaten verwalten)
- coach (darf Kaempfer verwalten und anmelden)
- member (nur Leserechte oder eingeschraenkte Rechte)

## 4.4 Berechtigungsregeln

- Kaempfer lesen: Vereinsmitglieder mit entsprechender Rolle
- Kaempfer bearbeiten: coach/admin/owner
- Vereinsmitglieder verwalten: admin/owner
- Vereinsuebergreifende Sicht nur mit expliziter Freigabe

Umsetzung ueber Laravel Policies und Gates.

---

## 5. Fachmodule

## 5.1 Modul Auth

- Login, Registrierung, Passwort-Reset
- Optional: Email-Verifizierung
- Optional: 2FA

## 5.2 Modul Clubs

- Verein anlegen, bearbeiten
- Mitglieder einladen
- Rollen vergeben
- Vereinswechsel/Mehrfachzuordnung

## 5.3 Modul Fighters

- Kaempferprofil CRUD
- Historie und Status
- Zugriffsmodell ueber Vereinsrollen

## 5.4 Modul Events

- Veranstaltung anlegen/bearbeiten/absagen
- Klassen, Gewicht, Fristen
- Veranstalter- und Vereinsbezug

## 5.5 Modul Registrations

- Kaempfer zu Veranstaltungen einschreiben/abmelden
- Pruefregeln (Altersklasse, Gewicht, Limits)
- Wartelistenlogik optional

## 5.6 Modul Messaging

- Email an Teilnehmer
- Email an Veranstalter
- Queue-basierter Versand mit Logging

## 5.7 Modul Export

- Teilnehmerlisten als CSV/XLSX/PDF
- Vereinsspezifische Exporte

---

## 6. Datenbankentwurf (Start)

## 6.1 Tabellen (neu oder angepasst)

- users
- clubs
- club_user
  - user_id
  - club_id
  - role
  - joined_at
- fighters
  - club_id (oder via fighter_club)
  - created_by_user_id
- events
  - created_by_user_id
  - organizer_club_id (nullable)
- registrations
  - fighter_id
  - event_id
  - status
  - registered_by_user_id

## 6.2 Migration vom Legacy-System

Empfohlenes Vorgehen:

1. Legacy-Datenbank analysieren und Mapping definieren
2. ETL-Skripte fuer Nutzer, Kaempfer, Veranstaltungen, Einschreibungen
3. Historische IDs in mapping_tabellen speichern
4. Dubletten und unvollstaendige Datensaetze kennzeichnen
5. Wiederholbare Import-Jobs bauen (idempotent)

---

## 7. API-Strategie (Drittprojekte)

## 7.1 API-Design

- REST API unter /api/v1
- JSON als Standard
- Konsistente Fehlerstruktur
- Pagination, Filter, Sortierung von Beginn an

## 7.2 Authentifizierung

- Laravel Sanctum Personal Access Tokens
- Token Scopes (zum Beispiel fighters:read, events:write)

## 7.3 Beispielressourcen

- POST /api/v1/auth/token
- GET /api/v1/clubs
- GET /api/v1/clubs/{id}/fighters
- POST /api/v1/events
- POST /api/v1/events/{id}/registrations

## 7.4 Integrationssicherheit

- Rate Limiting pro Client
- Audit-Logging fuer API-Schreibzugriffe
- Webhooks fuer Drittprojekte (spaeter)

---

## 8. UI/Frontend-Ansatz

Verbindliche Referenz fuer alle UI-Entscheidungen:
- [UI/UX Leitlinien](architecture/ui-ux-leitlinien.md)

Option A (schneller Start):

- Blade + Laravel Form Requests + Alpine.js

Option B (spaetere SPA):

- Inertia.js mit Vue oder React

Empfehlung:

- Start mit Blade/Inertia-Hybrid, um Legacy-Funktionen schnell zu migrieren
- API stabil halten, damit spaeter Mobile/App-Clients einfach angebunden werden

---

## 9. Sicherheitskonzept

- Passwort-Hashing mit bcrypt/argon2 (kein md5)
- CSRF-Schutz auf Web-Routen
- Strikte Input-Validierung ueber Form Requests
- SQL-Injection-Schutz durch Eloquent/Query Builder und Bindings
- Zugriffsschutz ueber Policies
- Geheimnisse nur in .env, nie im Repository
- Security Header und Logging

---

## 10. GitHub-Faehigkeit und DevOps

## 10.1 Repository-Setup

- Git-Repository im Ordner baseforfight
- .gitignore fuer Laravel
- .editorconfig, PHP-CS-Fixer/Pint, phpstan
- README mit Setup und Architekturueberblick
- CONTRIBUTING und Pull-Request-Template

## 10.2 Branching und Releases

- main (stabil)
- develop (Integration)
- feature/*, fix/*, chore/*
- Semantische Versionierung

## 10.3 CI (GitHub Actions)

Pipeline bei PR und Push:

1. Composer install
2. Lint/Format check
3. Static Analysis (phpstan)
4. Tests (Unit/Feature)
5. Optional: Security Audit

---

## 11. Migrations-Roadmap in Phasen

## Phase 0 - Foundation

- Neues Laravel-Projekt initialisieren
- CI, Code-Qualitaet, Basisrechte, Auth
- Club-Modell und Rollenmodell anlegen

## Phase 1 - User und Club

- Registrierung/Login
- Verein anlegen und Mitgliederverwaltung
- Rollen und Berechtigungen finalisieren

## Phase 2 - Fighters

- Kaempfer CRUD mit Vereinsfreigabe
- Legacy-Datenimport fuer Kaempfer

## Phase 3 - Events und Registrations

- Veranstaltungen und Einschreibungen
- Fristen, Limits, Absagen
- Exportfunktionen

## Phase 4 - Messaging und API v1

- Mailprozesse queue-basiert
- API-Endpunkte fuer Drittintegration
- API-Dokumentation (OpenAPI)

## Phase 5 - Go-Live

- Parallelbetrieb Alt/Neu
- Datenabgleich
- Umschaltung und Monitoring

---

## 12. Abwaertskompatibilitaet und Risiken

Hauptrisiken:

- Inhomogene Legacy-Daten
- Unklare Altregeln in verstreuten Skripten
- Rollenrechte koennen zu Beginn zu weit oder zu eng sein

Gegenmassnahmen:

- Fachregeln zuerst als Akzeptanztests modellieren
- Fruehe Pilotierung mit 1-2 Vereinen
- Audit-Logs fuer alle schreibenden Aktionen

---

## 13. Erste Umsetzungsartefakte (empfohlen)

- ADR-001 Modularisierungsstrategie
- ADR-002 Vereins- und Rollenmodell
- ADR-003 API Versioning und Auth
- Datenmapping-Dokument Legacy zu Laravel
- Testkatalog fuer Kernprozesse

---

## 14. Kurzfazit

Dieses Konzept ueberfuehrt BaseForFight in eine modulare Laravel-Architektur, behaelt die Kernprozesse bei und erweitert das System gezielt um Vereinskollaboration.
Damit wird das Projekt langfristig wartbar, sicherer, API-faehig und sauber ueber GitHub weiterentwickelbar.
