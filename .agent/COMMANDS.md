# Commands

This file is service-specific for `widewebblog/service`.

The repository is a Laravel 13 backend with Composer, Pest, Vite assets, database-backed cache/queue defaults, and an explicit `ai` queue for long-running AI work.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

## Development

```bash
php artisan serve
php artisan queue:work --queue=ai,default
php artisan schedule:work
npm run dev
```

## Database

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed
php artisan topics:prune-low-score
```

## Testing

```bash
php artisan test
php artisan test --filter=ExampleTest
composer test
```

Run the smallest meaningful test command first.

## Code Quality

```bash
vendor/bin/pint
vendor/bin/phpstan analyse
```

## Cache And Config

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear
```

## API Docs

If OpenAPI or Swagger tooling exists in the repository, document and use the actual supported command.

Do not invent API documentation commands.

## Execution Rules

- Work from `widewebblog/service` unless the task explicitly requires otherwise.
- Avoid heavy or broad commands when a narrower validation command is enough.
- Record planned and executed validation in `.agent/tasks/current-task.md`.
- Redis is not required for default local development in this repository.
