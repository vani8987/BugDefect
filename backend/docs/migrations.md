# Migrations

Файлы:

- `database/migration.php`
- `database/Migrations/*.php`
- `command.php`

`MigrationManager` применяет, откатывает и пересоздает миграции.

## Команды

```bash
php command.php migrate:run
php command.php migrate:down
php command.php migrate:fresh
```

## Таблица migrations

При старте manager создает таблицу:

```text
migrations(id, name, batch, executed_at)
```

Уже примененные файлы повторно не запускаются.

## Порядок

Файлы берутся из `database/Migrations`, фильтруются по расширению `.php` и сортируются по имени.

Класс миграции определяется по имени файла без числового префикса:

```text
001_CreateTableRoles.php -> database\Migrations\CreateTableRoles
```

## Rollback

`migrate:down` откатывает последний batch.

`migrate:fresh` откатывает все примененные миграции и запускает их заново.
