# Task Manager API

Минимальный REST API для задач на Symfony 6.4, PHP 8.3 и PostgreSQL 16.

## Запуск

```sh
git clone https://github.com/kkonstantin08/skyeng-task-manager.git
cd skyeng-task-manager
docker compose up --build -d
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

API будет доступно по адресу `http://localhost:8000`.

```sh
docker compose ps
docker compose logs -f app
docker compose down
```

`docker compose down` сохраняет данные PostgreSQL в именованном volume. Команда `docker compose down -v` удаляет этот volume вместе с данными.

## API

Все тела запросов и ответы используют JSON. Ошибки валидации имеют форму `{"errors":{"field":["message"]}}`; остальные ошибки — `{"error":"message"}`.

```sh
# Статусы и отдельный статус
curl http://localhost:8000/api/statuses
curl http://localhost:8000/api/statuses/1

# Создать статус
curl -i -X POST http://localhost:8000/api/statuses \
  -H 'Content-Type: application/json' \
  -d '{"name":"code_review","title":"Ревью кода"}'

# Создать задачу: начальный статус будет new
curl -i -X POST http://localhost:8000/api/tasks \
  -H 'Content-Type: application/json' \
  -d '{"title":"Подготовить отчет","description":"Отчет по продажам"}'

# Список задач, фильтр по имени статуса и одна задача
curl http://localhost:8000/api/tasks
curl 'http://localhost:8000/api/tasks?status=done'
curl http://localhost:8000/api/tasks/1

# Сменить статус и удалить задачу
curl -i -X PATCH http://localhost:8000/api/tasks/1/status \
  -H 'Content-Type: application/json' \
  -d '{"status":"done"}'
curl -i -X DELETE http://localhost:8000/api/tasks/1

# Удалить статус, если он не используется задачами
curl -i -X DELETE http://localhost:8000/api/statuses/4
```

Успешное создание возвращает `201`, чтение — `200`, удаление — `204`. Некорректный JSON возвращает `400`, отсутствующая задача или статус — `404`, дубликат имени статуса, попытка удалить используемый статус или статус `new` — `409`, ошибки полей — `422`.

## Архитектурные решения

- Запрос проходит через Controller, входной DTO и Symfony Validator; сервис содержит бизнес-правила, репозиторий — запросы к данным.
- `Task.status` — обязательная связь ManyToOne с `Status`. Имя статуса уникально в базе; миграция создаёт `new`, `in_progress` и `done`.
- Новая задача получает статус `new`; удалить его нельзя, даже пока он не используется задачами, иначе создание новых задач перестанет работать.
- API возвращает временные поля задачи как `created_at` и `updated_at`; `updated_at` меняется при смене статуса.
- Удаление используемого статуса возвращает `409`; внешний ключ запрещает удаление на уровне PostgreSQL, задачи не удаляются каскадно.
- PHP built-in server выбран как упрощение для разработки и тестового задания. Это не production-конфигурация.

## Что улучшить при наличии времени

- Добавить небольшой набор автоматических API/integration тестов для успешных сценариев и критических ошибок.
- Для production выбрать подходящий веб-сервер/runtime, управление секретами, аутентификацию и наблюдаемость.

## Использование ИИ и проверка

ИИ-ассистент использовался при разработке и проверке реализации. Все изменения и поведение API проверены вручную; перед отправкой выполнены Docker-сборка, миграции на чистой базе, `doctrine:schema:validate`, `lint:container` и HTTP smoke-запросы через `curl`. PHPUnit в проект не добавлялся.

Фактически затраченное время: **16 часов**.
