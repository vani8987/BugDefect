# Документация Mini Framework

Эта папка описывает классы и команды собственного mini-framework, на котором построен backend BugDefect.

## Разделы

- `router.md` — роутинг, параметры маршрутов, middleware и запуск контроллеров.
- `response.md` — отправка JSON-ответов.
- `request.md` — получение данных из HTTP-запроса, JSON body и сессии.
- `logger.md` — системное логирование Core-классов.
- `controller.md` — базовый класс контроллеров и общая валидация.
- `connect-db.md` — подключение к базе данных через PDO и `.env`.
- `create-table.md` — создание таблиц и изменение колонок в миграциях.
- `crud.md` — базовые операции с таблицами и правила безопасности.
- `migrations.md` — запуск миграций через `MigrationManager`.
- `commands.md` — консольные команды из `command.php`.
- `auth.md` — регистрация, вход, сессии и защищённые маршруты.

## Роль Core

`Core` — ядро backend-приложения. В нём лежат классы, которые не относятся к конкретной бизнес-логике, но нужны всему API: маршруты, middleware, запросы, ответы, подключение к базе, CRUD-операции, логирование и инструменты для миграций.

Бизнес-логика проекта находится выше — в `app/Controllers`, `app/Models`, `app/Middleware`, `Routes/api.php` и миграциях.

## Router и Middleware

`Router` регистрирует маршруты через `Router::route()` и вызывает нужный контроллер. Маршрут может принимать middleware пятым аргументом:

```php
Router::route(
    '/api/boards/{boardId}',
    'GET',
    [BoardsController::class, 'getBoard'],
    false,
    [BoardMiddleware::class, ['userAuth', 'boardAccess']]
);
```

Если маршрут содержит параметры, например `{boardId}`, `Router` передаёт их в middleware-методы, которые ожидают аргументы. Поэтому `userAuth()` вызывается без аргументов, а `boardAccess($boardId)` получает ID доски.

Базовый `Core\Middleware` хранит общие зависимости middleware: `Request` и `Logger`. Конкретные middleware приложения лежат в `app/Middleware`.
