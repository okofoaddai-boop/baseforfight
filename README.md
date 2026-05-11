# BaseForFight 2.0

BaseForFight 2.0 ist das neue Laravel-Fundament fuer die Migration des Legacy-Systems.

## Tech stack

- Laravel 10 (laeuft mit der vorhandenen CLI auf diesem System)
- PHP 8.3+
- MySQL
- Redis (vorbereitet fuer spaetere Queue/Cache-Nutzung)
- Sanctum fuer API-Tokens

## Aktuelle Basisstruktur

```text
app/Modules/
  Auth/
  Clubs/
  Fighters/
  Events/
  Registrations/
  Messaging/
  Billing/
  Admin/
  Shared/
database/migrations/
docs/architecture/
docs/api/
.github/workflows/
```

## Lokales Setup (Laragon)

1. Composer Dependencies installieren:

	```bash
	php composer.phar install
	```

2. `.env` ist bereits vorbereitet mit lokalen DB-Daten.

3. App Key generieren:

	```bash
	php artisan key:generate
	```

4. Datenbank erstellen und Migrationen ausfuehren:

	```bash
	php artisan migrate
	```

5. Optional Entwicklung starten:

	```bash
	php artisan serve
	```

## API Startpunkt

- Base: `/api/v1`
- Endpunkte:
  - `GET /api/v1/health`
	- `POST /api/v1/auth/token`
	- `DELETE /api/v1/auth/token` (auth:sanctum)
  - `GET /api/v1/me` (auth:sanctum)
	- `GET|POST|PATCH /api/v1/clubs` (auth:sanctum)
	- `GET /api/v1/clubs/{club}/members` (auth:sanctum)
	- `PATCH|DELETE /api/v1/clubs/{club}/members/{user}` (auth:sanctum)
	- `GET|POST /api/v1/clubs/{club}/invitations` (auth:sanctum)
	- `POST /api/v1/clubs/invitations/accept` (auth:sanctum)
	- `GET|POST|PATCH /api/v1/fighters` (auth:sanctum)
	- `GET|POST|PATCH /api/v1/events` (auth:sanctum)
	- `POST /api/v1/events/{event}/cancel` (auth:sanctum)
	- `GET|POST|PATCH|DELETE /api/v1/registrations` (auth:sanctum)

## Datenmodell (Foundation)

- `clubs`
- `club_user` (Rollen: `owner`, `admin`, `coach`, `member`)
- `fighters`
- `events`
- `registrations`

## Qualitaet und CI

- Code Style: Laravel Pint
- Statische Analyse: PHPStan
- Tests: `php artisan test`
- GitHub Actions Workflow unter `.github/workflows/ci.yml`

## Dokumentation

- Konzept: `docs/baseforfight-laravel-konzept.md`
- ADRs: `docs/architecture/`
- API Notes: `docs/api/README.md`
- OpenAPI: `docs/api/openapi.yaml`

## Brand Assets

- Zentrale Quellen: `resources/brand/logos/` und `resources/brand/icons/`
- Browser-Auslieferung: `public/assets/brand/`
- Konfiguration der Standarddateien: `config/brand.php`
