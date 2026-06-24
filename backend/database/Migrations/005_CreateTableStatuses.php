<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateTableStatuses extends CreateTable
{
    public function __construct()
    {
        parent::__construct('statuses');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'title VARCHAR(100) NOT NULL',
            'board_id INT NOT NULL',
            'FOREIGN KEY (board_id) REFERENCES boards (id)',
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
