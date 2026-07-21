# Auth

Файл: `Core/Auth.php`

`Auth` отвечает за регистрацию, вход, выход, проверку сессии и получение текущего пользователя.

## Зависимости

```php
public function __construct(
    Request|UserProviderInterface $request,
    Request|UserProviderInterface|null $modelBD = null,
    ?Logger $logger = null
)
```

Поддерживаются оба варианта:

```php
new Auth($request, $userModel, $logger);
new Auth($userModel);
```

Основной runtime получает `Auth` из container.

## UserProviderInterface

Модель пользователя должна реализовать:

```php
findByEmail(string $email): ?array
find(array $columns, int $id): ?array
create(array $nameColumns, array $values): bool
```

В проекте это `App\Models\User`.

## Пароли

Перед `password_hash()` пароль дополняется HMAC:

```php
hash_hmac('sha256', $password, HASH_KEY_PASSWORD)
```

`HASH_KEY_PASSWORD` обязателен в `.env`.

## Методы

- `registerByEmail($password, $mail)` - создает пользователя.
- `loginByEmail($password, $mail)` - проверяет пароль и записывает `auth_user_id` в session.
- `checkUser()` - проверяет наличие пользователя в session.
- `requireAuth()` - бросает `RuntimeException` с code `401`, если пользователь не авторизован.
- `user($columns)` - возвращает данные текущего пользователя.
- `logout()` - очищает `auth_user_id`.

## Использование В Router

`Router` получает `Auth` через container и может вызвать `requireAuth()` для маршрутов с auth flag. В текущих API-маршрутах основные проверки доступа вынесены в middleware.
