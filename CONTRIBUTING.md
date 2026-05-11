# Contributing

## Branch strategy

- `main`: stable releases
- `develop`: integration branch
- `feature/*`, `fix/*`, `chore/*`: topic branches

## Local setup

1. Install dependencies with `composer install`.
2. Copy env file with `copy .env.example .env` on Windows.
3. Generate app key with `php artisan key:generate`.
4. Configure DB connection in `.env`.
5. Run migrations with `php artisan migrate`.

## Quality gates

Run these checks before opening a pull request:

- `./vendor/bin/pint --test`
- `./vendor/bin/phpstan analyse`
- `php artisan test`
