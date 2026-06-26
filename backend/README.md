# BugDefect Backend

Backend - PHP API на собственном мини-фреймворке. Он отвечает за авторизацию, доски, участников, приглашения, уведомления и работу с MySQL.

## Стек

- PHP 8.3
- PDO MySQL
- Composer autoload PSR-4
- `vlucas/phpdotenv`
- Собственные классы `Router`, `Request`, `Response`, `CRUD`, `MigrationManager`

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

Файл `.env.example` содержит базовый шаблон:

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
```

Для Docker используется хост `mysql` и порт `3306` внутри сети контейнеров. Для запуска без Docker укажи параметры своей локальной MySQL.

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
GET    /api/boards/{boardId}/members
POST   /api/board/{boardId}/invite

GET    /api/notifications
PATCH  /api/notifications/read
```

Защищённые маршруты используют сессию пользователя.

## Структура

```
app/Controllers/       контроллеры API
app/Models/            модели таблиц
Core/                  ядро мини-фреймворка
database/Migrations/   миграции таблиц
Routes/api.php         регистрация API маршрутов
public/index.php       HTTP entrypoint и CORS
log/                   runtime логи
docs/                  дополнительная документация ядра
```

## База данных

Миграции создают роли, пользователей, доски, участников досок, статусы, дефекты, уведомления и приглашения на доску.

Приглашения хранятся в `board_invites` со статусом `pending`, `accepted` или `declined`. Уведомления хранятся отдельно в `notification` и могут ссылаться на приглашение через JSON-поле `data`.
