# ConnectDB

Файл: `Core/ConnectDB.php`

`ConnectDB` создает PDO-подключение к MySQL и хранит его в `$this->pdo`.

## Конструктор

```php
public function __construct(?Logger $logger = null)
{
    $this->logger = $logger ?? new Logger('database.log');
    $this->loadEnv();
    $this->pdo = $this->connectDB();
}
```

## Env

Используются переменные:

```env
DB_HOST=mysql
DB_PORT=3306
DB_NAME=bug_defect
DB_USER=root
DB_PASSWORD=root
```

Если `.env` существует в `backend/.env`, класс загрузит его через `vlucas/phpdotenv`.

## PDO

DSN:

```text
mysql:host={DB_HOST};port={DB_PORT};dbname={DB_NAME};charset=utf8mb4
```

PDO работает с `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.
