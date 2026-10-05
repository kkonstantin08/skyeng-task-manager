# Learning Plan — study the finished project

This plan starts only after the mandatory application is working.

There are no day numbers. Progress by understanding, not calendar time.

## L0 — Map the finished system

Goal: understand what exists before reading details.

Cover:

- repository structure
- how to run the project
- request path at a high level
- what PHP, Symfony, Doctrine, PostgreSQL and Docker each do
- where the entry point is
- what Composer does

No deep syntax yet.

Checkpoint target: developer can explain the system in 1–2 minutes.

## L1 — PHP syntax needed by this repository

Use actual project examples plus tiny unrelated examples.

Cover only syntax present in the repository, including as applicable:

- `<?php`
- `$variables`
- scalar/object types
- nullable types
- return types
- arrays and `=>`
- classes/properties/methods
- constructor
- `public` / `private`
- `->` / `::`
- namespaces and `use`
- constructor property promotion if used
- attributes `#[...]`
- exceptions
- `DateTimeImmutable`
- Composer autoloading

Do not teach PHP broadly beyond what the code uses.

## L2 — Docker, Compose and project startup

Use the real Dockerfile and Compose file.

Cover:

- image vs container
- Dockerfile instructions actually used
- Compose service
- build
- port mapping
- environment variables
- volume
- PostgreSQL service
- network/service hostname
- `docker compose up/down/logs/exec`
- why built-in PHP server is used here

Small exercise: explain every non-trivial line of the actual Dockerfile/Compose file.

## L3 — Database + Doctrine model

Trace `Task` and `Status`.

Cover:

- table/row/column
- primary key
- foreign key
- unique constraint
- nullability
- ORM concept
- Entity
- mapping attributes
- `ManyToOne`
- Repository
- EntityManager at a conceptual level
- migration
- `persist()` vs `flush()`

Change-impact examples:

- make description required
- rename a column
- add priority

## L4 — Status API request flow

Start with the simpler resource.

Trace each real endpoint through:

`HTTP -> Controller -> DTO/Validation -> Service -> Repository/Entity -> JSON`

Cover:

- route attributes
- Request/Response/JsonResponse
- dependency injection/autowiring
- DTO purpose
- Validator
- HTTP status codes
- 404 / 409 / 422

Ask the developer to explain the full request flow without looking after the walkthrough.

## L5 — Create Task

Trace `POST /api/tasks` end to end.

Cover:

- JSON decoding
- DTO creation/validation
- default status lookup
- Task construction
- relation assignment
- timestamps
- persistence
- `201 Created`

Change-impact questions:

- default should become `in_progress`
- status should optionally be supplied at creation
- description becomes required

## L6 — Read and filter Tasks

Trace:

- `GET /api/tasks`
- `GET /api/tasks?status=done`
- `GET /api/tasks/{id}`

Cover:

- route parameters
- query parameters
- repository lookup
- filtering
- entity serialization strategy used in this project
- why unknown ID is 404

Include only the SQL concepts necessary to understand what Doctrine is effectively doing.

## L7 — Update status and deletion rules

Trace:

- `PATCH /api/tasks/{id}/status`
- `DELETE /api/tasks/{id}`
- `DELETE /api/statuses/{id}`

Cover:

- business rule location
- entity state changes
- update timestamp
- deleting an entity
- why in-use Status deletion is 409
- foreign key implications

Change-impact examples:

- only allow `new -> in_progress -> done`
- soft-delete tasks
- forbid deleting default statuses

## L8 — Error handling and validation

Review every required bad path:

- malformed JSON
- invalid field values
- duplicate status
- unknown IDs
- unknown status name
- delete conflict

Goal: developer can predict the HTTP status and code path before executing a request.

## L9 — README and architecture defense

Developer must be able to defend every README decision:

- why ManyToOne
- why Service layer
- why DTO
- why repository
- why no Nginx/PHP-FPM
- why no auth/pagination
- why status delete is 409
- trade-offs and next improvements
- how AI was used

## L10 — Optional features actually present

Only study this if the project contains them:

- PHPUnit/API tests
- OpenAPI/Swagger

Do not teach features that were not added.

## L11 — Interview transition

Before INTERVIEW MODE, developer should be able to:

- start project from scratch;
- explain each major directory;
- explain one complete request flow without help;
- locate where a behavior change belongs;
- add a small field or validation rule with documentation only.
