# Router

Путь к Core-файлу:

`Core/Router.php`

## Назначение

`Router` получает URL и HTTP-метод, ищет подходящий маршрут, запускает middleware и вызывает нужный метод контроллера.

Если маршрут не найден, возвращается JSON-ответ с ошибкой `404`. Ошибки маршрутов и middleware записываются в `log/system.log`.

## Регистрация маршрута

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

1. URL маршрута.
2. HTTP-метод.
3. Массив `[ControllerClass::class, 'methodName']`.
4. Старый флаг `auth` для обратной совместимости.
5. Middleware-конфигурация или `null`.

## Параметры маршрута

Маршрут может содержать динамические параметры:

```php
Router::route('/api/boards/{boardId}/members/{userId}', 'DELETE', [BoardMemberController::class, 'deleteUser']);
```

Для запроса `/api/boards/3/members/10` контроллер получит:

```php
$controller->deleteUser('3', '10');
```

Параметры передаются по порядку появления в URL.

## Middleware

Middleware передаётся пятым аргументом:

```php
[BoardMiddleware::class, ['userAuth', 'boardAdmin']]
```

`Router` создаёт объект middleware и вызывает методы по порядку:

1. `userAuth()`
2. `boardAdmin($boardId)`

Если любой middleware-метод возвращает `false`, запрос останавливается и API возвращает `401 Unauthorized`.

## Передача параметров в middleware

`Router` использует `ReflectionMethod`, чтобы понять, сколько аргументов ожидает middleware-метод.

Например маршрут:

```php
/api/boards/{boardId}/members/{userId}
```

даёт параметры:

```php
['3', '10']
```

Если middleware-метод такой:

```php
public function userAuth(): bool
```

он получит `0` аргументов.

Если метод такой:

```php
public function boardAdmin(string|int $boardId): bool
```

он получит только первый параметр маршрута — `boardId`.

Это позволяет использовать одну цепочку middleware:

```php
[BoardMiddleware::class, ['userAuth', 'boardAdmin']]
```

без лишних аргументов для `userAuth()`.

## Как работает dispatch

1. Получает URL из `$_SERVER['REQUEST_URI']`.
2. Получает HTTP-метод из `$_SERVER['REQUEST_METHOD']`.
3. Перебирает зарегистрированные маршруты.
4. Преобразует маршрут с `{params}` в регулярное выражение.
5. Проверяет совпадение URL и метода.
6. Извлекает динамические параметры маршрута.
7. Проверяет существование контроллера и метода.
8. Выполняет middleware, если они указаны.
9. Создаёт контроллер и вызывает нужный метод с параметрами URL.
10. Если совпадения нет, отправляет `404`.
