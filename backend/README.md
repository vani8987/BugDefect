# BugDefect Backend

Backend — PHP API на собственном mini-framework. Он отвечает за авторизацию, доски, участников, приглашения, уведомления, статусы и дефекты.

## Стек

- PHP 8.3
- PDO MySQL
- Redis session storage
- Composer autoload PSR-4
- `vlucas/phpdotenv`
- Собственные классы `Router`, `Middleware`, `Request`, `Response`, `CRUD`, `MigrationManager`
- Docker Compose для локальной инфраструктуры

## Запуск через Docker

Из корня проекта:

```bash
docker compose up --build
docker compose exec backend php command.php migrate:run
```

API доступен на `http://localhost:8000/api`.

## Локальный запуск

```bash
composer install
cp .env.example .env
php command.php migrate:run
php command.php serve
```

Команда `serve` запускает PHP dev server на `http://localhost:8000`.

## Переменные окружения

Базовый шаблон лежит в `backend/.env.example`.

```env
APP_NAME=BugDefect
APP_ENV=dev
APP_DEBUG=true

DB=mysql
DB_HOST=mysql
DB_PORT=3306
DB_NAME=bug_defect
DB_USER=root
DB_PASSWORD=

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

Для Docker используется `DB_HOST=mysql` и порт `3306` внутри сети контейнеров. Для запуска без Docker нужно указать параметры локальной MySQL.

`SESSION_DRIVER=file` использует обычные PHP-сессии. `SESSION_DRIVER=redis` переносит хранение сессий в Redis. В Docker Compose backend подключается к Redis по `REDIS_HOST=redis`.

## Middleware и доступ

В backend добавлен отдельный middleware-слой:

- `Core\Middleware` — базовый класс middleware с доступом к `Request` и `Logger`.
- `AuthMiddleware::userAuth()` — проверяет авторизацию пользователя по сессии.
- `BoardMiddleware::boardAccess($boardId)` — проверяет, что пользователь является участником доски.
- `BoardMiddleware::boardAdmin($boardId)` — проверяет, что пользователь является администратором доски.

`Router` поддерживает middleware в маршрутах и передаёт параметры маршрута в методы middleware. Например, для `/api/boards/{boardId}` значение `boardId` может попасть в `boardAccess()` или `boardAdmin()`.

Контроллеры теперь отвечают в основном за валидацию входных данных и бизнес-логику, а повторяющиеся проверки доступа вынесены в middleware.

## Основные маршруты

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

GET    /api/status/{boardId}
POST   /api/status/{boardId}
PATCH  /api/status/{boardId}/position
DELETE /api/status/{boardId}/{statusId}

POST   /api/boards/{boardId}/defects
PATCH  /api/boards/{boardId}/defects/{defectId}/move

GET    /api/notifications
PATCH  /api/notifications/read
POST   /api/boards/{boardId}/invite/accept
POST   /api/boards/{boardId}/invite/reject
```

## Доски и участники

Пользователь видит только те доски, где он есть в `board_member`. Для страницы доски backend возвращает данные доски, роль текущего пользователя и количество участников.

Администратор доски может:

- удалить доску через `DELETE /api/boards/{boardId}`;
- удалить участника через `DELETE /api/boards/{boardId}/members/{userId}`;
- отправить приглашение через `POST /api/board/{boardId}/invite`;
- создавать, удалять и сортировать статусы;
- создавать и перемещать дефекты.

## Статусы доски

Статусы относятся к конкретной доске и хранят порядок через поле `position`.

Поддерживаются действия:

- `GET /api/status/{boardId}` — получить статусы доски с сортировкой по `position` и вложенными дефектами в `items`;
- `POST /api/status/{boardId}` — создать статус с `title`, `description` и `position`;
- `PATCH /api/status/{boardId}/position` — сохранить новый порядок статусов;
- `DELETE /api/status/{boardId}/{statusId}` — удалить статус конкретной доски.

Получать список статусов может участник доски. Создавать, удалять и менять порядок статусов может администратор доски.

## Дефекты

Дефекты относятся к доске и конкретному статусу. Порядок внутри статуса хранится через поле `position`.

Поддерживаются действия:

- `POST /api/boards/{boardId}/defects` — создать дефект с `title`, необязательным `description`, `statusId` и `executorID`;
- `PATCH /api/boards/{boardId}/defects/{defectId}/move` — перенести дефект в другой статус и сохранить новую позицию.

При создании backend проверяет принадлежность статуса доске и принадлежность исполнителя участникам доски. При переносе проверяется принадлежность дефекта и целевого статуса этой же доске.

Тело запроса переноса:

```json
{
  "statusId": 2,
  "position": 1
}
```

## Команды

```bash
php command.php migrate:run      # применить новые миграции
php command.php migrate:down     # откатить последнюю миграцию
php command.php migrate:fresh    # пересоздать базу миграциями
php command.php serve            # запустить API сервер
composer dump-autoload           # обновить autoload после новых классов
```

## Структура

```text
app/Controllers/       контроллеры API
app/Middleware/        middleware приложения
app/Models/            модели таблиц
Core/                  ядро mini-framework
database/Migrations/   миграции таблиц
Routes/api.php         регистрация API маршрутов
public/index.php       HTTP entrypoint и CORS
log/                   runtime логи
docs/                  дополнительная документация ядра
```

## База данных

Миграции создают роли, пользователей, доски, участников досок, статусы, дефекты, уведомления и приглашения на доску.

Ключевые связи:

- `board_member` связывает пользователей с досками и ролями;
- `statuses` хранит колонки доски и поле `position`;
- `defects` хранит задачи, `board_id`, `status_id`, исполнителя, автора и `position`;
- `board_invites` хранит приглашения со статусом `pending`, `accepted` или `declined`;
- `notification` хранит уведомления и JSON-поле `data`.

## Production

Перед production-запуском нужно заменить dev-секреты, настроить CORS под домен frontend, проверить права на директорию `log/`, выбрать `SESSION_DRIVER`, настроить Redis при необходимости и выполнить миграции на целевой базе.
