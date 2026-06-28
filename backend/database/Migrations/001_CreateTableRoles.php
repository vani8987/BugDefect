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
        $guestRoleName = $_ENV['BOARD_ROLE_GUEST'] ?? getenv('BOARD_ROLE_GUEST') ?: 'guest';
        $guestRoleName = str_replace("'", "''", $guestRoleName);

        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            "name VARCHAR(50) NOT NULL UNIQUE DEFAULT '{$guestRoleName}'",
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
