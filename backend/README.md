# BugDefect Backend

Backend - PHP API на собственном mini-framework. Он отвечает за авторизацию, доски, участников, приглашения, уведомления, статусы и дефекты.

## Стек

- PHP 8.3
- PDO MySQL
- Redis session storage
- Composer autoload PSR-4
- `vlucas/phpdotenv`
- Собственные классы `Router`, `Request`, `Response`, `CRUD`, `MigrationManager`
- Сессии для авторизации

## Запуск через Docker

Из корня проекта:

```bash
docker compose up --build
docker compose exec backend php command.php migrate:run
```

API будет доступен на `http://localhost:8000/api`.

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

Redis нужен, чтобы сессии не зависели от файлов внутри конкретного backend-контейнера. Это полезно при перезапуске контейнера и при будущем масштабировании backend на несколько инстансов.

## Команды

```bash
php command.php migrate:run      # применить новые миграции
php command.php migrate:down     # откатить последнюю миграцию
php command.php migrate:fresh    # пересоздать базу миграциями
php command.php serve            # запустить API сервер
composer dump-autoload           # обновить autoload после новых классов
```

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

Защищённые маршруты используют сессию пользователя.

## Доски и участники

Пользователь видит только те доски, где он есть в `board_member`. Для страницы доски backend возвращает данные доски, роль текущего пользователя и количество участников.

Администратор доски может:

- удалить доску через `DELETE /api/boards/{boardId}`;
- удалить участника через `DELETE /api/boards/{boardId}/members/{userId}`;
- отправить приглашение пользователю через `POST /api/board/{boardId}/invite`;
- управлять статусами и дефектами доски.

Перед изменением данных backend проверяет авторизацию, валидность ID, существование записей и права администратора.

## Приглашения и уведомления

При отправке приглашения backend создаёт запись в `board_invites` со статусом `pending` и уведомление типа `invite` для приглашённого пользователя.

При получении уведомлений backend добавляет в `data.status` актуальный статус приглашения из `board_invites`. Frontend по этому статусу показывает кнопки принятия/отклонения или итоговую строку.

## Статусы доски

Статусы относятся к конкретной доске и хранят порядок через поле `position`.

Поддерживаются действия:

- `GET /api/status/{boardId}` — получить статусы доски с сортировкой по `position` и вложенными дефектами в `items`;
- `POST /api/status/{boardId}` — создать статус с `title`, `description` и `position`;
- `PATCH /api/status/{boardId}/position` — сохранить новый порядок статусов;
- `DELETE /api/status/{boardId}/{statusId}` — удалить статус конкретной доски.

Создавать, удалять и менять порядок статусов может только администратор доски. Получать список статусов может любой участник доски.

## Дефекты

Дефекты относятся к доске и конкретному статусу. Порядок внутри статуса хранится через поле `position`.

Поддерживаются действия:

- `POST /api/boards/{boardId}/defects` — создать дефект с `title`, необязательным `description`, `statusId` и `executorID`;
- `PATCH /api/boards/{boardId}/defects/{defectId}/move` — перенести дефект в другой статус и сохранить новую позицию.

При создании backend проверяет авторизацию, существование доски, права администратора, принадлежность статуса доске и принадлежность исполнителя участникам доски. Новая позиция рассчитывается как последняя позиция в статусе + 1.

При переносе дефекта backend проверяет авторизацию, доску, права администратора, принадлежность дефекта доске, принадлежность целевого статуса доске и валидность новой позиции.

Тело запроса переноса:

```json
{
  "statusId": 2,
  "position": 1
}
```

## Структура

```text
app/Controllers/       контроллеры API
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

Перед production-запуском нужно заменить dev-секреты, настроить CORS под домен frontend, проверить права на директорию `log/` и выполнить миграции на целевой базе.
