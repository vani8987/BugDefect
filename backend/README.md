# BugDefect Backend

Backend - PHP API на собственном mini-framework. Он отвечает за авторизацию, доски, участников, приглашения, уведомления, статусы и дефекты.

## Стек

- PHP 8+
- Composer PSR-4 autoload
- `vlucas/phpdotenv`
- PDO MySQL
- PHP sessions или Redis sessions
- Собственные `Router`, `Container`, `Request`, `Response`, `Middleware`, `CRUD`, migrations

## Запуск Через Docker

Из корня проекта:

```bash
docker compose up --build
docker compose exec backend php command.php migrate:run
```

API доступен на `http://localhost:8000/api`.

## Локальный Запуск

```bash
composer install
php command.php migrate:run
php command.php serve
```

Команда `serve` запускает PHP dev server на `http://localhost:8000`.

## Переменные Окружения

Минимальный набор:

```env
DB_HOST=mysql
DB_PORT=3306
DB_NAME=bug_defect
DB_USER=root
DB_PASSWORD=root

HASH_KEY_PASSWORD=replace_with_a_long_random_secret

BOARD_ROLE_ADMIN=admin
BOARD_ROLE_DEVELOPER=developer
BOARD_ROLE_GUEST=guest

SESSION_DRIVER=file
SESSION_NAME=BUGDEFECTSESSID
SESSION_LIFETIME=86400

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DATABASE=0
REDIS_SESSION_PREFIX=bugdefect_session:
```

Для Docker используется `DB_HOST=mysql` и порт `3306` внутри сети контейнеров. С хоста MySQL доступен на `localhost:3307`.

## DI Container

Приложение собирается в `app/bootstrap/app.php`.

Точка входа `public/index.php`:

```php
$container = require_once __DIR__ . '/../app/bootstrap/app.php';
require_once __DIR__ . '/../Routes/api.php';

$router = $container->make(Router::class);
$router->dispatch();
```

В контейнере зарегистрированы:

- core-сервисы: `Logger`, `Request`, `Response`, `Auth`, `Router`;
- модели: `User`, `Roles`, `Boards`, `BoardsMember`, `Defect`, `Statuses`, `Notification`, `InviteBoardMember`;
- middleware: `AuthMiddleware`, `BoardMiddleware`;
- все API-контроллеры из `Routes/api.php`.

Контроллеры и middleware принимают зависимости через конструктор. Старый стиль `new Controller()` также остается рабочим благодаря fallback-значениям.

## Middleware И Доступ

- `AuthMiddleware::userAuth()` проверяет авторизацию пользователя по сессии.
- `BoardMiddleware::boardAccess($boardId)` проверяет членство пользователя в доске.
- `BoardMiddleware::boardAdmin($boardId)` проверяет роль администратора доски.

`Router` передает параметры маршрута в middleware-методы. Например, `{boardId}` попадает в `boardAccess($boardId)` и `boardAdmin($boardId)`.

## Основные Маршруты

```text
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/me

POST   /api/createBoard
GET    /api/boards
GET    /api/boards/{boardId}
DELETE /api/boards/{boardId}
GET    /api/boards/{boardId}/members
DELETE /api/boards/{boardId}/members/{userId}
POST   /api/board/{boardId}/invite

GET    /api/notifications
PATCH  /api/notifications/read
POST   /api/boards/{boardId}/invite/accept
POST   /api/boards/{boardId}/invite/reject

GET    /api/status/{boardId}
POST   /api/status/{boardId}
PATCH  /api/status/{boardId}/position
DELETE /api/status/{boardId}/{statusId}

POST   /api/boards/{boardId}/defects
PATCH  /api/boards/{boardId}/defects/{defectId}/move
```

## Команды

```bash
php command.php migrate:run
php command.php migrate:down
php command.php migrate:fresh
php command.php serve
composer dump-autoload
```

## Структура

```text
Core/                  ядро mini-framework
app/bootstrap/app.php  сборка контейнера
app/Controllers/       API-контроллеры
app/Middleware/        middleware приложения
app/Models/            модели таблиц
database/Migrations/   миграции
Routes/api.php         регистрация маршрутов
public/index.php       HTTP entrypoint и CORS
docs/                  документация framework
```

## Проверка

```bash
php -l Core/Container.php
php -l Core/Router.php
php -l app/bootstrap/app.php
php -l app/Controllers/AuthController.php
php -l app/Controllers/BoardsController.php
php -l app/Controllers/DefectsController.php
```
