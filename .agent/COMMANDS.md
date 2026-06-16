# Commands

This file is service-specific for `widewebblog/service`.

Use these commands as Laravel 13 defaults only after verifying the actual repository supports them.

Before running any command, check `composer.json`, `package.json`, `Makefile`, project docs, or `vendor/bin` when those files exist.

Current workspace note:

- No `composer.json` was present when this document was updated.
- No `package.json` was present when this document was updated.
- No `Makefile` was present when this document was updated.
- No project README was present when this document was updated.

Treat the commands below as expected Laravel service commands to confirm once the real repository files exist.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Development

```bash
php artisan serve
php artisan queue:work
php artisan schedule:work
```

## Database

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed
```

## Testing

```bash
php artisan test
php artisan test --filter=ExampleTest
```

Run the smallest meaningful test command first.

## Code Quality

Before running code quality commands, confirm the tool exists in `composer.json` or `vendor/bin`.

Typical examples:

```bash
vendor/bin/pint
vendor/bin/phpstan analyse
vendor/bin/phpunit
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
- Do not assume frontend tooling applies to this repository unless confirmed by project files.
