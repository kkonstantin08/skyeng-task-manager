# Build Plan

This is a dependency-based implementation plan, not a calendar.

Build the project first and teach later. Follow `docs/GIT_RULES.md` for autonomous repository initialization, commits and final GitHub publication.

## Batch 0 — Repository bootstrap and Docker

Before implementation, initialize Git on `main` if the repository does not exist. Do not create a separate empty initialization commit.

Deliver:

- Symfony 6.4 skeleton/API-ready project
- PHP 8.3 Dockerfile
- Docker Compose with app + PostgreSQL 16
- environment/database configuration
- Doctrine configured
- application reachable from host

Verify:

- image builds
- containers start
- Symfony runs
- database connection works

Typical commit:

`chore: Bootstrap Symfony application`

## Batch 1 — Domain model and database

Deliver:

- `Status` entity
- `Task` entity
- `Task -> Status` ManyToOne relation
- timestamps
- migration(s)
- initial `new`, `in_progress`, `done` statuses
- uniqueness of `Status.name`

Verify:

- clean DB migration works
- schema matches entities
- initial statuses exist

Typical commit:

`feat(domain): Add task and status entities`

## Batch 2 — Status API

Deliver all mandatory Status endpoints:

- list
- get by id
- create
- delete
- DTO + validation
- service/repository behavior
- `404`, `409`, `422` cases as applicable
- prevent deletion when used by Tasks

Verify with curl or equivalent.

Typical commit:

`feat(status): Implement status API`

## Batch 3 — Task API

Deliver all mandatory Task endpoints:

- create Task with default `new`
- list Tasks
- filter list by `status` name
- get by id
- change status by status name
- delete Task
- DTO + validation
- timestamps updated appropriately
- unknown Task/Status handling

Verify every endpoint with curl or equivalent.

Typical commit:

`feat(task): Implement task API`

## Batch 4 — Error handling and edge cases

Verify and fix:

- malformed JSON
- empty/missing title
- wrong input types where relevant
- invalid/unknown status name
- unknown IDs
- duplicate status name
- deleting used status
- consistent JSON errors
- correct HTTP status codes

Do not build an elaborate global exception architecture unless the existing code genuinely needs it.

Typical commit:

`fix(api): Harden validation and errors`

## Batch 5 — README and fresh-run verification

README must include:

- clone/start commands
- Docker Compose command
- migration command
- API URL
- curl examples or concise endpoint usage
- 3–7 architecture decisions/compromises
- Task/Status relation explanation
- behavior when deleting an in-use Status
- built-in PHP server trade-off
- what would be improved with more time
- honest AI usage
- placeholder/reminder for the developer to enter real time spent

Fresh verification:

1. stop containers;
2. remove relevant local DB volume if safe;
3. rebuild/start from README steps;
4. apply migrations;
5. run endpoint smoke checks;
6. ensure README instructions match reality.
7. review local Git history without squashing/rebasing it into one commit.
8. if GitHub authentication is available, create the public remote and push `main` according to `docs/GIT_RULES.md`.

Typical commit:

`docs: Complete setup and architecture guide`

## Optional Batch 6 — Tests

Only if mandatory work is already complete.

Prefer a small high-value API/integration test set covering:

- create Task
- filter Task by status
- patch status
- 404
- validation error
- deleting used status -> 409

Do not chase high coverage percentages.

## Optional Batch 7 — OpenAPI/Swagger

Only after tests or if the developer explicitly prefers it first.

Use one conventional Symfony-compatible package and keep configuration minimal.
