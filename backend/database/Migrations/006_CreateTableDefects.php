<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateTableDefects extends CreateTable
{
    public function __construct()
    {
        parent::__construct('defects');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'title VARCHAR(100) NOT NULL',
            'description TEXT NOT NULL',
            'executer_id INT NOT NULL',
            'FOREIGN KEY (executer_id) REFERENCES users (id)',
            'board_id INT NOT NULL',
            'FOREIGN KEY (board_id) REFERENCES boards (id)',
            'status_id INT NOT NULL',
            'FOREIGN KEY (status_id) REFERENCES statuses (id)',
            'appointed_id INT NOT NULL',
            'FOREIGN KEY (appointed_id) REFERENCES users (id)',
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
