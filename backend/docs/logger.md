# Logger

Файл: `Core/Logger.php`

`Logger` пишет runtime-события backend в log-файлы.

## Использование

```php
$logger = new Logger('system.log');
$logger->info('Route dispatched.');
$logger->warning('Unauthorized route access.');
$logger->error('Database connection failed.');
```

## DI

В container зарегистрирован общий logger:

```php
$container->bind(Logger::class, fn (): Logger => new Logger('system.log'));
```

Для отдельных областей bootstrap создает отдельные log-файлы:

- `database.log`
- `auth.log`
- `Board.log`
- `Defects.log`
- `Notification.log`

## Рекомендация

Core-классы должны принимать `?Logger $logger = null` и использовать fallback:

```php
$this->logger = $logger ?? new Logger('system.log');
```

Так класс можно использовать и через container, и вручную.
