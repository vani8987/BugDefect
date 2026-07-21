# Container

Файл: `Core/Container.php`

Bootstrap приложения: `app/bootstrap/app.php`

## Назначение

`Container` хранит правила создания объектов приложения. Он позволяет не создавать зависимости прямо внутри `Router`, controller или middleware.

Интерфейс:

```php
interface ContainerInterface
{
    public function bind(string $class, callable $fn): void;
    public function make(string $class): object;
}
```

## Регистрация

```php
$container->bind(Request::class, fn (Container $container): Request => new Request(
    $container->make(Logger::class)
));
```

`bind()` принимает имя класса и фабрику. В фабрику передается сам container, поэтому можно получать другие зависимости через `make()`.

## Создание

```php
$request = $container->make(Request::class);
```

Если binding найден, container вызывает фабрику. Если binding не найден, container пробует создать объект через `new $class()`.

## Bootstrap

`app/bootstrap/app.php` регистрирует:

- `Logger`
- `Request`
- `Response`
- `ConnectDB`
- все модели приложения
- `Auth`
- все controllers
- `AuthMiddleware`
- `BoardMiddleware`
- `Router`

Пример регистрации `Router`:

```php
$container->bind(Router::class, fn (Container $container): Router => new Router(
    $container->make(Auth::class),
    $container,
    $container->make(Logger::class),
    $container->make(Response::class),
));
```

## Использование В Entrypoint

```php
$container = require_once __DIR__ . '/../app/bootstrap/app.php';

require_once __DIR__ . '/../Routes/api.php';

$router = $container->make(Router::class);
$router->dispatch();
```

## Правило Для Новых Классов

Если controller, middleware или service получает зависимости, добавляй их в конструктор и регистрируй класс в `app/bootstrap/app.php`.

Fallback через `?? new ...` можно оставить для простых тестов и обратной совместимости.
