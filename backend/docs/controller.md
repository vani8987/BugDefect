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

## positiveId()

`positiveId()` проверяет типовой параметр id:

- значение должно быть integer-compatible;
- значение должно быть больше `0`;
- при ошибке отправляется JSON через `validate()`;
- при успехе возвращается уже приведенный `int`.

```php
$boardId = $this->positiveId('Board id is invalid', $boardId);

if ($boardId === null) {
    return;
}
```

Это заменяет повторяющиеся проверки вида:

```php
filter_var($boardId, FILTER_VALIDATE_INT) !== false && (int) $boardId > 0
```

## validateStringLength()

`validateStringLength()` проверяет строковое поле:

- значение должно быть строкой;
- если `$required = true`, строка не может быть пустой;
- длина после `trim()` не должна превышать `$countSymbol`;
- status по умолчанию `422`.

```php
if (!$this->validateStringLength($title, 'Title is required and must be at most 100 characters.', countSymbol: 100)) {
    return;
}

if (!$this->validateStringLength($description, 'Description must be at most 255 characters.', required: false)) {
    return;
}
```

Эти helper-ы нужны только для типовой HTTP-валидации. Проверки существования записей, результата удаления или бизнес-правил пока остаются в controller, а позже могут быть вынесены в service layer.
