<?php

namespace database\Migrations;

use Core\CreateTable;

class AddedColumnPositionInDefects extends CreateTable
{
    public function __construct()
    {
        parent::__construct('defects');
    }

    public function up(): void
    {
        $statement = $this->pdo->query("SHOW COLUMNS FROM `defects` LIKE 'position'");

        if ($statement !== false && $statement->fetch() !== false) {
            return;
        }

        $this->addColumn('position INT NOT NULL DEFAULT 1');
    }

    public function down(): void
    {
        return;
    }
}
