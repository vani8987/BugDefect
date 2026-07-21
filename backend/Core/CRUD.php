<?php
namespace Core;

use Exception;
use InvalidArgumentException;
use PDO;


interface ModelInterface {
    public function create(array $nameColumn, array $arrValues): bool;
    public function delete(int $id): bool;
    public function find(array $nameColumns, int $id): ?array;
    public function update(array $nameColumns, array $arrValues, int $id): bool;
    public function findAll(array $nameColumns, ?string $whereColumn = null, mixed $value = null): array;
    public function findOneBy(array $nameColumns, string $whereColumn, mixed $value): ?array;
    public function join(
        array $columnsName,
        string $joinTable,
        string $firstColumn,
        string $operator,
        string $secondColumn,
        ?string $whereColumn = null,
        mixed $whereValue = null,
        string $mode = 'INNER'
    );
}

class CRUD extends ConnectDB implements ModelInterface  {
    private string $name;

    public function __construct(string $name, ?Logger $logger = null) {
        parent::__construct($logger);
        $this->name = $this->quoteIdentifier($name);

    }

    private function validate(array $names, array $values): bool
    {
        if (empty($names)) {
            $this->logger->warning('CRUD validation failed: columns are empty.');
            return false;
        }

        if (empty($values)) {
            $this->logger->warning('CRUD validation failed: values are empty.');
            return false;
        }

        if (count($names) !== count($values)) {
            $this->logger->warning('CRUD validation failed: column and value counts differ.');
            return false;
        }

        return true;
    }

    private function quoteIdentifier(string $identifier): string
    {
        if (trim($identifier) === '') {
            throw new InvalidArgumentException('Identifier cannot be empty.');
        }

        return implode('.', array_map(
            fn(string $part) => '`' . str_replace('`', '``', $part) . '`',
            explode('.', $identifier)
        ));
    }

    private function quoteIdentifiers(array $identifiers): array
    {
        if (empty($identifiers)) {
            throw new InvalidArgumentException('The columns cannot be empty.');
        }

        return array_map(function (mixed $identifier): string {
            if ($identifier === '*') {
                return '*';
            }

            if (!is_string($identifier)) {
                throw new InvalidArgumentException('The column name must be a string.');
            }

            return $this->quoteIdentifier($identifier);
        }, $identifiers);
    }

    public function create(array $nameColumn, array $arrValues): bool {
        if (! $this->validate($nameColumn, $arrValues)) {
            return false;
        }

        try {
            $columns = implode(', ', $this->quoteIdentifiers($nameColumn));
            $values = implode(', ', array_fill(0, count($arrValues), '?'));

            $statement = $this->pdo->prepare("INSERT INTO {$this->name} ({$columns}) VALUES ({$values})");
            $statement->execute(array_values($arrValues));
            $this->logger->info("Record created in {$this->name}.");
            return true;
        } catch (Exception $err) {
            $this->logger->error('CRUD create failed: ' . $err->getMessage());
            return false;
        }
    }

    public function update(array $nameColumns, array $arrValues, int $id): bool {
        if (! $this->validate($nameColumns, $arrValues)) {
            return false;
        }

        try {
            $columns = $this->quoteIdentifiers($nameColumns);

            for ($index = 0; $index < count($columns); $index++) {
                $statement = $this->pdo->prepare("UPDATE {$this->name} SET {$columns[$index]} = ? WHERE `id` = ?");
                $statement->execute([$arrValues[$index], $id]);
            }

            $this->logger->info("Record updated in {$this->name}.");
            return true;
        } catch (Exception $err) {
            $this->logger->error('CRUD update failed: ' . $err->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $statement = $this->pdo->prepare("DELETE FROM {$this->name} WHERE `id` = ?");
            $statement->execute([$id]);
            $this->logger->info("Record deleted from {$this->name}.");
            return true;
        } catch (Exception $err) {
            $this->logger->error('CRUD delete failed: ' . $err->getMessage());
            return false;
        }
    }

    public function find(array $nameColumns, int $id): ?array {
        try {
            $columns = implode(', ', $this->quoteIdentifiers($nameColumns));

            $statement = $this->pdo->prepare("SELECT {$columns} FROM {$this->name} WHERE `id` = ?");
            $statement->execute([$id]);

            $this->logger->info("Record requested from {$this->name}.");
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result === false ? null : $result;
        } catch (Exception $err) {
            $this->logger->error('CRUD find failed: ' . $err->getMessage());
            return null;
        }
    }

    public function findOneBy(array $nameColumns, string $whereColumn, mixed $value): ?array {
        try {
            $columns = implode(', ', $this->quoteIdentifiers($nameColumns));
            $conditionColumn = $this->quoteIdentifier($whereColumn);

            $statement = $this->pdo->prepare(
                "SELECT {$columns} FROM {$this->name} WHERE {$conditionColumn} = ? LIMIT 1"
            );
            $statement->execute([$value]);

            $this->logger->info("Record requested from {$this->name} by {$whereColumn}.");
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return $result === false ? null : $result;
        } catch (Exception $err) {
            $this->logger->error('CRUD findOneBy failed: ' . $err->getMessage());
            return null;
        }
    }

    public function findAll(array $nameColumns, ?string $whereColumn = null, mixed $value = null): array {
        try {
            $columns = implode(', ', $this->quoteIdentifiers($nameColumns));
            $query = "SELECT {$columns} FROM {$this->name}";

            if ($whereColumn === null) {
                $statement = $this->pdo->query($query);
            } else {
                $conditionColumn = $this->quoteIdentifier($whereColumn);
                $statement = $this->pdo->prepare("{$query} WHERE {$conditionColumn} = ?");
                $statement->execute([$value]);
            }

            $this->logger->info("Records requested from {$this->name}.");
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            $this->logger->error('CRUD findAll failed: ' . $err->getMessage());
            return [];
        }
    }

    public function join(
        array $columnsName,
        string $joinTable,
        string $firstColumn,
        string $operator,
        string $secondColumn,
        ?string $whereColumn = null,
        mixed $whereValue = null,
        string $mode = 'INNER'
    ): array {
        try {
            if (empty($columnsName)) {
                $this->logger->warning('CRUD join failed: columns cannot be empty.');
                return [];
            }

            $allowedModes = ['INNER', 'LEFT', 'RIGHT'];
            $mode = strtoupper($mode);

            if (!in_array($mode, $allowedModes, true)) {
                $this->logger->warning("CRUD join failed: invalid join mode {$mode}.");
                return [];
            }

            $allowedOperators = ['=', '!=', '<>', '>', '<', '>=', '<='];

            if (!in_array($operator, $allowedOperators, true)) {
                $this->logger->warning("CRUD join failed: invalid operator {$operator}.");
                return [];
            }

            $columns = implode(', ', $this->quoteIdentifiers($columnsName));
            $joinTable = $this->quoteIdentifier($joinTable);
            $firstColumn = $this->quoteIdentifier($firstColumn);
            $secondColumn = $this->quoteIdentifier($secondColumn);

            $query = "
                SELECT {$columns}
                FROM {$this->name}
                {$mode} JOIN {$joinTable}
                ON {$firstColumn} {$operator} {$secondColumn}
            ";

            if ($whereColumn !== null) {
                $whereColumn = $this->quoteIdentifier($whereColumn);
                $query .= " WHERE {$whereColumn} = ?";

                $statement = $this->pdo->prepare($query);
                $statement->execute([$whereValue]);
            } else {
                $statement = $this->pdo->query($query);
            }

            $this->logger->info("Join requested from {$this->name}.");
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            $this->logger->error('CRUD join failed: ' . $err->getMessage());
            return [];
        }
    }

}
