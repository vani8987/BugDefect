# Response

Файл: `Core/Response.php`

`Response` отправляет JSON-ответы.

## Конструктор

```php
public function __construct(?Logger $logger = null)
{
    $this->logger = $logger ?? new Logger('system.log');
}
```

## JSON

```php
$response->json([
    'message' => 'Board created',
], 201);
```

Метод:

- выставляет HTTP status code;
- добавляет `Content-Type: application/json; charset=UTF-8`;
- кодирует массив через `json_encode()`;
- при ошибке кодирования возвращает `500`.

## Рекомендация

Controllers должны возвращать ошибки и успешные ответы только через `Response::json()`, чтобы формат API оставался единым.
