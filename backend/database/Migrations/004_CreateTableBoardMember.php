<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateTableBoardMember extends CreateTable
{
    public function __construct()
    {
        parent::__construct('board_member');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'board_id INT NOT NULL',
            'FOREIGN KEY (board_id) REFERENCES boards (id)',
            'user_id INT NOT NULL',
            'FOREIGN KEY (user_id) REFERENCES users (id)',
            'role_id INT NOT NULL',
            'FOREIGN KEY (role_id) REFERENCES roles (id)',
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
