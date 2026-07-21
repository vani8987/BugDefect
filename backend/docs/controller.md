# Controller

Файл: `Core/Controller.php`

Базовый controller хранит общие зависимости:

- `Request`
- `Response`
- `Logger`

## Конструктор

```php
public function __construct(Logger $logger, Response $response, Request $request)
{
    $this->request = $request;
    $this->logger = $logger;
    $this->response = $response;
}
```

Конкретные controllers принимают зависимости через optional-параметры и передают core-зависимости в `parent::__construct()`.

Пример:

```php
public function __construct(
    ?Request $request = null,
    ?Response $response = null,
    ?Logger $logger = null,
    ?Boards $boards = null
) {
    $request = $request ?? new Request();
    $response = $response ?? new Response();
    $this->boards = $boards ?? new Boards();

    parent::__construct($logger ?? new Logger('Board.log'), $response, $request);
}
```

## validate()

`validate()` проверяет условие и, если оно ложно:

- пишет ошибку в logger;
- отправляет JSON с `message`;
- возвращает `false`.

```php
if (!$this->validate($board !== null, 'Board was not found.', 404)) {
    return;
}
```

Это общий способ останавливать controller action при ошибке входных данных или бизнес-логики.
