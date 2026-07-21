# Router

Файл: `Core/Router.php`

`Router` регистрирует API-маршруты, ищет подходящий route для текущего HTTP-запроса, запускает middleware и вызывает controller.

## Регистрация Маршрута

```php
Router::route(
    '/api/boards/{boardId}',
    'GET',
    [BoardsController::class, 'getBoard'],
    false,
    [BoardMiddleware::class, ['userAuth', 'boardAccess']]
);
```

Аргументы:

- route pattern;
- HTTP method;
- `[ControllerClass::class, 'methodName']`;
- `auth` flag для `Auth::requireAuth()`;
- optional middleware: `[MiddlewareClass::class, ['methodOne', 'methodTwo']]`.

## Параметры Маршрута

Параметры в `{name}` превращаются в regex-группы. Значения передаются:

- в controller method;
- в middleware methods по количеству параметров метода.

Например, `{boardId}` попадет в `boardAccess($boardId)`.

## Middleware

`Router` создает middleware через DI container:

```php
$middleware = $this->container->make($middlewareClass);
```

Если middleware возвращает `false`, Router отвечает `401 Unauthorized`.

## Controller

Controller тоже создается через container:

```php
$controller = $this->container->make($class);
$controller->$functionClass(...$matches);
```

Поэтому controller должен быть зарегистрирован в `app/bootstrap/app.php`, если ему нужны зависимости.

## Ответы На Ошибки

- `404` - route не найден.
- `500` - controller/middleware/method не существует.
- `401` - auth или middleware отклонили запрос.
