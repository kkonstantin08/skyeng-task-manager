# Skyeng Test Task — working specification

This file is a concise working copy of the requirements from the supplied assignment. If any wording conflicts with the original document, the original document wins.

## Goal

Build a small REST API Task Manager.

## Task entity

- id
- title
- description
- status
- created_at
- updated_at

## Status entity

- id
- name
- title

## API

### Create Task

`POST /api/tasks`

Example body:

```json
{
  "title": "Подготовить отчет",
  "description": "Отчет по продажам за май"
}
```

### List Tasks

`GET /api/tasks`

Filter by status:

`GET /api/tasks?status=done`

### Get Task

`GET /api/tasks/{id}`

### Delete Task

`DELETE /api/tasks/{id}`

### Change Task status

`PATCH /api/tasks/{id}/status`

Example body:

```json
{
  "status": "done"
}
```

### List Statuses

`GET /api/statuses`

### Get Status

`GET /api/statuses/{id}`

### Create Status

`POST /api/statuses`

Example body:

```json
{
  "name": "code_review",
  "title": "Ревью кода"
}
```

### Delete Status

`DELETE /api/statuses/{id}`

## Required stack

- PHP 8+
- Symfony 6+
- PostgreSQL
- Docker Compose
- REST API
- Git

## Submission expectations

- public GitHub/GitLab repository
- small meaningful commits; do not squash history
- README with startup steps
- README with 3–7 architecture decisions/trade-offs
- README: what would be improved with more time
- README: AI usage and how results were checked
- README: actual time spent

## Additional points

- correct HTTP status codes
- OpenAPI/Swagger
- strict input validation
- PHPUnit/Codeception tests
- DTO/architectural patterns
- cache/queues
- AGENTS.md/CLAUDE.md, MCP servers, plugins or agent workflow rules as optional process/documentation improvements; they do not justify unrelated production code

Optional features must not compromise the mandatory base.

## Reviewer focus

- project starts from README in one or two commands plus migrations as documented
- malformed JSON
- nonexistent IDs/statuses
- deleting a Status used by Tasks
- readable typed code
- controllers do not contain raw SQL or all business logic
- architecture matches README

## Explicitly unnecessary

- pagination
- complex authorization
- admin panel
- external deployment

## Follow-up stage

If the solution passes, the developer will show the code/API, explain decisions and make a small change in the repository without AI assistance. Documentation is allowed.
