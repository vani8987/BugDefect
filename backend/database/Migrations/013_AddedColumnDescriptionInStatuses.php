<?php

namespace database\Migrations;

use Core\CreateTable;

class AddedColumnDescriptionInStatuses extends CreateTable
{
    public function __construct()
    {
        parent::__construct('statuses');
    }

    public function up(): void
    {
        $statement = $this->pdo->query("SHOW COLUMNS FROM `statuses` LIKE 'description'");

        if ($statement !== false && $statement->fetch() !== false) {
            return;
        }

        $this->addColumn("description VARCHAR(255) NOT NULL DEFAULT ''");
    }

    public function down(): void
    {
        return;
    }
}
