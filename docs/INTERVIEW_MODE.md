# Interview Mode

Purpose: prepare for the follow-up stage where the developer must show the project, explain decisions and make a small change without an AI assistant.

## Rules for Codex

- Do not proactively edit production code.
- Use the actual repository, not generic Symfony trivia.
- Ask one question at a time when doing an oral-style simulation.
- Do not reveal the answer before the developer attempts it.
- For a wrong answer: hint -> second attempt -> explanation.
- Gradually reduce hints during the session.

## Part 1 — Architecture defense

Ask questions such as:

- Trace `POST /api/tasks` from HTTP request to database.
- Why is `Task.status` a relation rather than a string?
- Why is business logic not inside the controller?
- What does the DTO protect/separate?
- What does Doctrine do for us?
- What is a migration and why not edit the database manually?
- Why does deleting an in-use Status return 409?
- Why is the built-in PHP server acceptable here but not the proposed production setup?

Require answers using actual class/file names from the repository.

## Part 2 — Code reading

Select real methods from the repository and ask:

- What does this method do?
- What is the type of this value?
- What happens on this branch?
- What does this Symfony/Doctrine attribute mean?
- What happens before/after `flush()`?
- Why was this dependency injected?

## Part 3 — Change-impact analysis

Before coding, ask the developer to name every likely file/layer that should change.

Example changes:

- add `priority` to Task;
- make `description` required;
- allow filtering by two statuses;
- make default status configurable;
- prevent deleting `new` status;
- restrict status transitions.

Evaluate architecture reasoning before syntax.

## Part 4 — Small no-AI implementation

Assign one realistic change.

Codex should not implement it.

Allowed assistance progression:

1. developer works alone with documentation;
2. if stuck, Codex may point to the relevant subsystem/file only;
3. if still stuck, explain the concept but do not paste the solution;
4. only after the exercise is complete, review the diff.

## Exit criteria

Interview readiness is good when the developer can:

- explain the project without memorized wording;
- predict where changes belong;
- read PHP syntax used in the repository;
- make one small end-to-end modification;
- diagnose common validation/404/409 mistakes;
- run the project and migrations from README.
