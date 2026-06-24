<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateTableRoles extends CreateTable
{
    public function __construct()
    {
        parent::__construct('roles');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            "name VARCHAR(50) NOT NULL UNIQUE DEFAULT 'guest'",
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
