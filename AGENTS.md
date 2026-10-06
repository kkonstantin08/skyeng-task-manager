# Repository Guide

## Scope and stack

This repository contains a small Task Manager REST API for the Skyeng backend
internship task. It uses PHP 8.3, Symfony 6.4, Doctrine ORM and Migrations,
PostgreSQL 16, Docker Compose, and the PHP built-in development server.

## Architecture and conventions

- Keep request handling in controllers, request input and constraints in DTOs,
  business rules in services, data queries in repositories, and persisted
  state in entities.
- Preserve the required `Task` ManyToOne relation to `Status`. New tasks use
  the `new` status; that status cannot be deleted.
- Use English for code and identifiers. Prefer explicit types and readable
  code. Do not add tutorial comments or abstractions that only pass arguments
  through.
- Keep the implementation minimal. Do not add optional features, dependencies,
  or architecture layers without a concrete task requirement.
- Keep API errors as JSON. Malformed JSON returns `400`, missing resources
  `404`, conflicts `409`, and semantic validation errors `422`. Validate
  untrusted input types explicitly.
- Task JSON timestamps use `created_at` and `updated_at`; PHP names remain
  camelCase.

## Checks

Run the application and database with `docker compose up --build -d`, then
apply migrations and validate the Doctrine mapping:

```sh
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec app php bin/console doctrine:schema:validate
docker compose exec app php bin/console lint:container
```

The PHP built-in server is for development and this test task, not production.
Do not remove the PostgreSQL volume unless a clean-database run is intended.

## Git

- Work on `main` unless a separate branch is needed for a concrete reason.
- Use concise English Conventional Commit subjects, such as
  `fix(api): Protect the default status`.
- Keep commits coherent; do not rewrite existing history.
- Do not put tool attribution in branch names or Git metadata.
