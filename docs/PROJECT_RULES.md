# Project Rules

## Fixed stack

- PHP 8.3
- Symfony 6.4 LTS
- PostgreSQL 16
- Doctrine ORM
- Doctrine Migrations
- Symfony Validator
- Docker Compose
- PHP built-in development server

## Required architecture

Keep it small:

`Controller -> DTO/Validation -> Service -> Repository -> Entity`

A layer may be skipped for a trivial operation if adding it would only create a pass-through class, but the overall code must keep controllers free from raw SQL and large business logic.

## Domain model

### Task

- `id`
- `title`
- `description`
- `status` -> `Status` relation
- `createdAt`
- `updatedAt`

### Status

- `id`
- `name`
- `title`

Relation: many Tasks may reference one Status (`ManyToOne`).

Initial statuses:

- `new`
- `in_progress`
- `done`

New Task default: `new`.

## Mandatory endpoints

- `POST /api/tasks`
- `GET /api/tasks`
- `GET /api/tasks?status=done`
- `GET /api/tasks/{id}`
- `DELETE /api/tasks/{id}`
- `PATCH /api/tasks/{id}/status`
- `GET /api/statuses`
- `GET /api/statuses/{id}`
- `POST /api/statuses`
- `DELETE /api/statuses/{id}`

## Response/status-code policy

- create success: `201`
- get/list success: `200`
- delete success: `204`
- malformed JSON: `400`
- unknown Task/Status: `404`
- conflict such as duplicate Status name or deleting used Status: `409`
- field/semantic validation error: `422`

Keep JSON error shapes simple, for example:

```json
{"error": "Task not found"}
```

Validation may use:

```json
{
  "errors": {
    "title": ["This value should not be blank."]
  }
}
```

Exact wording may vary, but it must be consistent.

## Explicit non-goals

Do not add unless explicitly approved:

- authentication/authorization
- frontend/Twig/forms
- admin panel
- pagination
- external deployment
- Redis/cache
- queues/Messenger
- Nginx
- PHP-FPM
- CQRS
- DDD architecture
- event sourcing
- complex generic exception framework
- generic base repositories/services
- unnecessary interfaces

## Optional work order

Only after all mandatory requirements and README are complete:

1. PHPUnit/API tests
2. OpenAPI/Swagger
3. further improvements only if they clearly help
