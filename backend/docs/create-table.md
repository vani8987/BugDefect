# CreateTable

Файл: `Core/CreateTable.php`

`CreateTable` используется в миграциях для создания таблиц и изменения колонок.

## Конструктор

```php
public function __construct(string $name, ?Logger $logger = null)
{
    parent::__construct($logger);
    $this->name = $name;
}
```

## Использование

Миграции лежат в `database/Migrations`.

Пример:

```php
$table = new CreateTable('boards');
$table->id();
$table->string('title', 100);
$table->integer('owner_id');
$table->timestamps();
$table->create();
```

Конкретный набор helper-методов смотри в `Core/CreateTable.php`.

## Рекомендация

Новые миграции называй с числовым префиксом:

```text
015_AddSomething.php
```

`MigrationManager` сортирует файлы по имени.
