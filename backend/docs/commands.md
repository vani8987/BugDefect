# Commands

Файл: `command.php`

CLI-команды backend:

```bash
php command.php migrate:run
php command.php migrate:down
php command.php migrate:fresh
php command.php serve
```

## migrate:run

Применяет новые миграции из `database/Migrations`.

## migrate:down

Откатывает последний batch миграций.

## migrate:fresh

Откатывает все примененные миграции и запускает их заново.

Используй осторожно: команда пересоздает схему и может удалить данные.

## serve

Запускает PHP dev server:

```bash
php -S localhost:8000 -t public
```
