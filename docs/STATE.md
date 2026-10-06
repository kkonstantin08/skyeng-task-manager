# Repository State

mode: BUILD
mandatory_build_status: in_progress
optional_tests_status: not_started
optional_openapi_status: not_started

## Current build batch

Batch 3 — Task API

## Completed batches

- Batch 0 — Symfony 6.4.47, PHP 8.3.33 + PostgreSQL 16 Docker Compose stack; verified app startup, Symfony console, database connection and HTTP response.
- Batch 1 — Task/Status entities, Task→Status relation, repositories, unique status name and initial migration with seeded statuses.
- Batch 2 — Status list/get/create/delete endpoints, DTO validation, duplicate-name conflict and in-use deletion guard.

## Working features

- Symfony application starts at http://localhost:8000.
- PostgreSQL connection works from the app container.
- Task references Status through a required ManyToOne relation; deleting a referenced row is restricted by PostgreSQL.
- Initial statuses `new`, `in_progress`, `done` are present.
- Status API supports list, get, create and delete.

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

## Git

Last commit: 727b0e3 feat(domain): Add task and status entities; Batch 2 commit pending
Repository: local `main`; GitHub identity configured from authenticated account

## Known issues

Task endpoints are not implemented yet.

## Next action

Implement Batch 3: Task create/list/filter/get/delete/status update endpoints.

## Handoff rule

Update this file whenever a meaningful build batch completes or before ending a chat mid-batch. Keep it concise and factual so another chat can resume without the old conversation. Record each autonomous batch commit hash/message here.
