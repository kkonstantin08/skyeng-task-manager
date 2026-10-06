# Repository State

mode: BUILD
mandatory_build_status: complete
optional_tests_status: not_started
optional_openapi_status: not_started

## Current build batch

Batch 5 — README and fresh-run verification

## Completed batches

- Batch 0 — Symfony 6.4.47, PHP 8.3.33 + PostgreSQL 16 Docker Compose stack; verified app startup, Symfony console, database connection and HTTP response.
- Batch 1 — Task/Status entities, Task→Status relation, repositories, unique status name and initial migration with seeded statuses.
- Batch 2 — Status list/get/create/delete endpoints, DTO validation, duplicate-name conflict and in-use deletion guard.
- Batch 3 — Task create/list/filter/get/delete/status-update endpoints; default `new` status and timestamp updates.
- Batch 4 — Hardened JSON parsing and validation errors; query parameter type check; consistent `400/404/409/422` responses.
- Batch 5 — README startup flow completed; clean-volume fresh-run passed, including all mandatory endpoint smoke checks.

## Working features

- Symfony application starts at http://localhost:8000.
- PostgreSQL connection works from the app container.
- Task references Status through a required ManyToOne relation; deleting a referenced row is restricted by PostgreSQL.
- Initial statuses `new`, `in_progress`, `done` are present.
- Status API supports list, get, create and delete.
- Task API supports create/list/filter/get/delete/status update.
- Malformed JSON, wrong field types, invalid query types and required fields return JSON errors.
- README contains the tested startup commands, endpoint examples, architecture choices and submission placeholders.

## Verification

- `docker compose up --build -d` — app and PostgreSQL start; DB healthcheck passes.
- `docker compose exec -T app php bin/console about` — Symfony 6.4.47 / PHP 8.3.33.
- `docker compose exec -T app php bin/console doctrine:query:sql 'SELECT 1 AS connected'` — DB connected.
- `curl http://localhost:8000/` — Symfony returns HTTP 404 (no route defined yet).
- `docker compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction` — clean schema migrated successfully.
- `docker compose exec -T app php bin/console doctrine:schema:validate` — mappings and database schema are in sync.
- `docker compose exec -T app php bin/console doctrine:query:sql 'SELECT name, title FROM status ORDER BY id'` — all three initial statuses present.
- `docker compose exec -T app php bin/console lint:container` — all services are wired.
- Status endpoint smoke checks: list/get `200`, create `201`, delete `204`, missing ID `404`, malformed JSON `400`, validation `422`, duplicate name `409`.
- Task endpoint smoke checks: create `201` with default `new`, list/filter/get `200`, status change `200`, delete `204`, missing task/status `404`, used-status deletion `409`.
- Status change updates `updatedAt`; timestamps are stored and returned at whole-second precision by Doctrine DBAL.
- Edge smoke checks: query array and non-object JSON `422`; invalid description and missing title `422`; used-status deletion `409`; malformed PATCH for a missing Task returns `404`.
- Fresh-run: `docker compose down --volumes`, README `docker compose up --build -d`, README migration command; 18 HTTP endpoint/error assertions passed on the clean database.
- `docker compose exec -T app php bin/console doctrine:schema:validate` — mappings and database schema are in sync after fresh migration.
- `docker compose exec -T app php bin/console lint:container` — all services are wired after fresh run.

## Git

Last commit: 918c8b4 docs: Complete setup and architecture guide; fresh-run state commit pending
Repository: local `main`; GitHub identity configured from authenticated account

## Known issues

Optional automated tests and OpenAPI were not added; mandatory scope is complete.

## Next action

Create and push the public GitHub repository from authenticated account `kkonstantin08`, then record its URL in this file.

## Handoff rule

Update this file whenever a meaningful build batch completes or before ending a chat mid-batch. Keep it concise and factual so another chat can resume without the old conversation. Record each autonomous batch commit hash/message here.
