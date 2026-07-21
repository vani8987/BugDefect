# Request

Файл: `Core/Request.php`

`Request` читает данные из разных источников HTTP-запроса и session.

## Конструктор

```php
public function __construct(?Logger $logger = null)
{
    $this->logger = $logger ?? new Logger('system.log');
}
```

Через container обычно передается общий `Logger`.

## Методы

```php
$request->getDataJson('title');
$request->getDataBody('name');
$request->getDataUrl('page');
$request->getDataCookie('theme');
$request->getDataSession('auth_user_id');
```

Если источник пустой, ключ отсутствует или JSON некорректный, метод возвращает `null` и пишет запись в лог.

## JSON

`getDataJson()` читает `php://input`, декодирует JSON через `JSON_THROW_ON_ERROR` и ожидает объект.

Пример тела запроса:

```json
{
  "title": "Login bug",
  "description": "Cannot login with valid password"
}
```

## Session

`getDataSession()` запускает `session_start()`, если сессия еще не активна.

Настройка session выполняется раньше в `public/index.php` через `SessionManager::configure()`.
