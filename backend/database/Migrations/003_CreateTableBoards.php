<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateTableBoards extends CreateTable
{
    public function __construct()
    {
        parent::__construct('boards');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'title VARCHAR(100) NOT NULL',
            'owner_id INT NOT NULL',
            'FOREIGN KEY (owner_id) REFERENCES users (id)',
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
