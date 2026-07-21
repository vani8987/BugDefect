# Middleware

Файлы:

- `Core/Middleware.php`
- `app/Middleware/AuthMiddleware.php`
- `app/Middleware/BoardMiddleware.php`

## Core Middleware

Базовый middleware хранит:

- `Request`
- `Logger`

```php
public function __construct(?Request $request = null, ?Logger $logger = null)
{
    $this->logger = $logger ?? new Logger('Middleware.log');
    $this->request = $request ?? new Request();
}
```

## AuthMiddleware

`AuthMiddleware::userAuth()` проверяет, что в session есть `auth_user_id` и такой пользователь существует.

Используется для маршрутов, где нужен авторизованный пользователь.

## BoardMiddleware

`BoardMiddleware` наследуется от `AuthMiddleware`.

Методы:

- `boardAccess($boardId)` - пользователь должен быть участником доски.
- `boardAdmin($boardId)` - пользователь должен быть администратором доски.

`Router` передает `boardId` из маршрута, например `/api/boards/{boardId}`.

## DI

Middleware создаются через container:

```php
$container->bind(BoardMiddleware::class, fn (Container $container): BoardMiddleware => new BoardMiddleware(
    $container->make(Request::class),
    $container->make(Logger::class),
    $container->make(User::class),
    $container->make(Boards::class),
    $container->make(BoardsMember::class),
));
```
