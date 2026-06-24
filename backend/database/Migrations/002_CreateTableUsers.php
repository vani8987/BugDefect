<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateTableUsers extends CreateTable
{
    public function __construct()
    {
        parent::__construct('users');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'name VARCHAR(100)',
            'email VARCHAR(255) NOT NULL UNIQUE',
            'password VARCHAR(255) NOT NULL',
            'role_id INT NOT NULL DEFAULT 1',
            'FOREIGN KEY (role_id) REFERENCES roles (id)',
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
