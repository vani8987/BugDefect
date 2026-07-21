# Mini Framework Docs

Эта папка описывает backend mini-framework, на котором работает BugDefect API.

## Разделы

- `container.md` - DI container и `app/bootstrap/app.php`.
- `router.md` - регистрация маршрутов, dispatch, middleware и создание controller через container.
- `request.md` - получение данных из JSON, POST, GET, cookies и session.
- `response.md` - JSON-ответы.
- `controller.md` - базовый controller и общий helper `validate()`.
- `middleware.md` - базовый middleware и middleware приложения.
- `auth.md` - регистрация, вход, сессия и текущий пользователь.
- `connect-db.md` - подключение к MySQL через PDO.
- `crud.md` - базовые CRUD-операции моделей.
- `create-table.md` - helper для миграций.
- `migrations.md` - `MigrationManager` и CLI-команды миграций.
- `commands.md` - команды из `command.php`.
- `logger.md` - логирование.

## Основной Поток Запроса

1. `public/index.php` загружает Composer autoload, `.env`, session config и CORS.
2. `public/index.php` подключает `app/bootstrap/app.php` и получает `Container`.
3. `Routes/api.php` регистрирует маршруты через `Router::route()`.
4. `Router` находит маршрут по URL и HTTP-методу.
5. `Router` создает middleware и controller через `Container`.
6. Controller валидирует входные данные, вызывает модели и возвращает JSON через `Response`.

## DI Container

Новые зависимости регистрируются в `app/bootstrap/app.php`:

```php
$container->bind(Service::class, fn (Container $container): Service => new Service(
    $container->make(Dependency::class)
));
```

Если класс используется в маршруте, лучше зарегистрировать его в bootstrap, чтобы `Router` создал его со всеми зависимостями.

## Обратная Совместимость

Core-классы, модели, middleware и контроллеры сохраняют fallback-значения:

```php
$this->request = $request ?? new Request();
```

Поэтому классы можно создавать вручную в простых примерах и тестах, но основной runtime должен идти через container.
