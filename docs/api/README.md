# API Notes

## Base URL

- Local: `http://baseforfight.test/api/v1`

## Initial endpoints

- `GET /health`
- `POST /auth/token` (email/password + device_name)
- `GET /me` (requires Sanctum token)
- `DELETE /auth/token` (revokes current token)
- `GET /clubs` (only clubs where user is member)
- `POST /clubs` (creates club and owner membership)
- `GET /clubs/{id}` (member only)
- `PATCH /clubs/{id}` (owner/admin only)
- `GET /clubs/{id}/members` (member only)
- `PATCH /clubs/{id}/members/{userId}` (owner/admin)
- `DELETE /clubs/{id}/members/{userId}` (owner/admin)
- `GET /clubs/{id}/invitations` (owner/admin)
- `POST /clubs/{id}/invitations` (owner/admin)
- `POST /clubs/invitations/accept` (authenticated user)
- `GET /fighters` (fighters from member clubs)
- `POST /fighters` (owner/admin/coach)
- `GET /fighters/{id}` (member only)
- `PATCH /fighters/{id}` (owner/admin/coach)
- `GET /events` (clubs where user is member)
- `POST /events` (owner/admin)
- `GET /events/{id}` (member of organizer club)
- `PATCH /events/{id}` (owner/admin)
- `POST /events/{id}/cancel` (owner/admin)
- `GET /registrations` (by fighter club membership)
- `POST /registrations` (owner/admin/coach)
- `GET /registrations/{id}` (member of fighter club)
- `PATCH /registrations/{id}` (owner/admin/coach)
- `DELETE /registrations/{id}` (owner/admin/coach)

## Authorization rules (current)

- club roles: `owner`, `admin`, `coach`, `member`
- fighter write access: `owner`, `admin`, `coach`
- club update access: `owner`, `admin`
- event create/update/cancel: `owner`, `admin`
- invitation role assignment by owner: any role
- invitation role assignment by admin: `coach`, `member`

## Next steps

- Consistent error payload format
- Pagination and filtering conventions

## OpenAPI

- Contract file: `docs/api/openapi.yaml`
