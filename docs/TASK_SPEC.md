# Task Manager — API Specification

## Purpose and scope

Task Manager is a JSON REST API for creating, listing, reading, and deleting tasks, changing a task's status, and managing available statuses. This document describes the behavior implemented in the repository's `main` branch as reviewed on 9 October 2026. The original Skyeng assignment remains authoritative for assessment if its requirements differ from this document.

The service uses PHP 8.3, Symfony 6.4, Doctrine ORM/Migrations, PostgreSQL 16, and Docker Compose. It has no web frontend or authentication layer.

## Data model

### Task

| Field | API representation | Rules |
| --- | --- | --- |
| `id` | integer | Generated primary key |
| `title` | string | Required; not blank after trimming for validation; max. 255 characters |
| `description` | string or `null` | Optional; max. 5,000 characters when provided |
| `status` | string | Existing status **name**; defaults to `new` on creation |
| `created_at` | ISO 8601 date-time string | Set when the task is created |
| `updated_at` | ISO 8601 date-time string | Set on creation and refreshed when the status is changed |

Each task has a required many-to-one relation to `Status`, stored as `status_id` in PostgreSQL. The foreign key uses `ON DELETE RESTRICT`.

### Status

| Field | API representation | Rules |
| --- | --- | --- |
| `id` | integer | Generated primary key |
| `name` | string | Required, unique; max. 50 characters; `^[a-z][a-z0-9_]*$` |
| `title` | string | Required; not blank after trimming for validation; max. 100 characters |

The initial migration inserts three statuses: `new`, `in_progress`, and `done`. Further valid statuses may be created through the API. `new` cannot be deleted. Any status referenced by tasks is also protected from deletion.

## HTTP API

All successful resource responses use JSON. The list endpoints return arrays, and the single-resource endpoints return objects. ID route parameters accept non-negative decimal digit sequences and are resolved to integer IDs.

| Method | Path | Operation | Success response |
| --- | --- | --- | --- |
| `POST` | `/api/tasks` | Create task in `new` status | `201` + task, `Location` header |
| `GET` | `/api/tasks` | List tasks in ascending ID order | `200` + array |
| `GET` | `/api/tasks?status=done` | List tasks filtered by status name | `200` + array |
| `GET` | `/api/tasks/{id}` | Get task | `200` + task |
| `PATCH` | `/api/tasks/{id}/status` | Set an existing status by name | `200` + updated task |
| `DELETE` | `/api/tasks/{id}` | Delete task | `204` + empty body |
| `GET` | `/api/statuses` | List statuses in ascending ID order | `200` + array |
| `GET` | `/api/statuses/{id}` | Get status | `200` + status |
| `POST` | `/api/statuses` | Create status | `201` + status, `Location` header |
| `DELETE` | `/api/statuses/{id}` | Delete an unused, non-default status | `204` + empty body |

### Request examples

Create a task:

```http
POST /api/tasks
Content-Type: application/json

{"title":"Prepare report","description":"Sales report for May"}
```

Change its status:

```http
PATCH /api/tasks/1/status
Content-Type: application/json

{"status":"done"}
```

Create a status:

```http
POST /api/statuses
Content-Type: application/json

{"name":"code_review","title":"Code review"}
```

Example task response (timestamps and IDs are illustrative):

```json
{
  "id": 1,
  "title": "Prepare report",
  "description": "Sales report for May",
  "status": "new",
  "created_at": "2026-10-09T12:00:00+00:00",
  "updated_at": "2026-10-09T12:00:00+00:00"
}
```

No endpoint exists to edit a task's title or description, update status metadata, or restore deleted resources.

## Validation and errors

| Code | Conditions |
| --- | --- |
| `400 Bad Request` | Malformed JSON request body |
| `404 Not Found` | Task or status ID not found; unknown status name in status change or task-list filter |
| `409 Conflict` | Duplicate status name; attempt to delete `new` or a status currently assigned to tasks |
| `422 Unprocessable Entity` | JSON body is not an object; invalid/missing field; non-string `status` query parameter |
| `500 Internal Server Error` | The `new` status is missing when task creation is attempted |

Field validation errors use the shape:

```json
{"errors":{"title":["This value should not be blank."]}}
```

Other application errors use `{"error":"message"}`. Unknown input properties are not explicitly rejected by the current DTO mapping; only the recognized fields are processed.

## Design and implementation notes

- Controllers map routes, validate inputs, and serialize responses.
- Readonly DTOs define validation constraints with Symfony Validator.
- Services implement task/status operations; repositories implement Doctrine lookups and filtered queries.
- `JsonPayload` rejects malformed JSON and non-object top-level JSON bodies.
- PostgreSQL enforces unique status names and restricts deletion of referenced statuses.
- Swagger UI is served at `/api/doc`; the generated OpenAPI JSON document is at `/api/doc.json`. The specification is produced using NelmioApiDocBundle, OpenAPI attributes, and shared schemas in `config/packages/nelmio_api_doc.yaml`.
- No pagination, authentication, queue, cache integration, admin interface, or deployment configuration is included.
- No PHPUnit/Codeception test suite is currently configured. API behavior can be checked manually with `curl` and Swagger UI.

## Running and reviewing

```sh
docker compose up --build -d
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec app php bin/console doctrine:schema:validate
docker compose exec app php bin/console lint:container
```

The API listens on `http://localhost:8000`. The default development setup runs on PHP's built-in server, not a production web server.

Review the normal task/status lifecycle and negative cases: malformed JSON, wrong input types, missing records, duplicate status names, unknown status filters, deleting a status in use, and deleting `new`. Automated coverage would be a useful future improvement.

The README contains startup instructions, architecture decisions, and any development-process or time-spent disclosures required by the assignment. Such disclosures should describe the actual work performed and verification carried out rather than inferred history.
