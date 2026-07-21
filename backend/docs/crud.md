# CRUD

Файл: `Core/CRUD.php`

`CRUD` - базовая модель таблицы. Наследуется от `ConnectDB`.

## Конструктор

```php
public function __construct(string $name, ?Logger $logger = null)
{
    parent::__construct($logger);
    $this->name = $this->quoteIdentifier($name);
}
```

Пример модели:

```php
class Boards extends CRUD
{
    public function __construct(?Logger $logger = null)
    {
        parent::__construct('boards', $logger);
    }
}
```

## Методы

- `create($columns, $values): bool`
- `update($columns, $values, $id): bool`
- `delete($id): bool`
- `find($columns, $id): ?array`
- `findOneBy($columns, $whereColumn, $value): ?array`
- `findAll($columns, $whereColumn = null, $value = null): array`
- `join(...)`

## Безопасность

Значения передаются через prepared statements.

Имена таблиц и колонок экранируются через backticks. Для колонок разрешен `*`.

## Ошибки

При ошибке метод пишет запись в logger и возвращает безопасное значение:

- `false` для write-операций;
- `null` для поиска одной записи;
- `[]` для списков.
