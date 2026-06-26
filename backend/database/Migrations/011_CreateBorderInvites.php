<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateBorderInvites extends CreateTable
{
    public function __construct()
    {
        parent::__construct('board_invites');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            'board_id INT NOT NULL',
            'FOREIGN KEY (board_id) REFERENCES boards (id)',
            'invited_user_id INT NOT NULL',
            'FOREIGN KEY (invited_user_id) REFERENCES users (id)',
            'inviter_user_id INT NOT NULL',
            'FOREIGN KEY (inviter_user_id) REFERENCES users (id)',
            'role_id INT NOT NULL',
            'FOREIGN KEY (role_id) REFERENCES roles (id)',
            'created DATETIME DEFAULT CURRENT_TIMESTAMP',
            'status VARCHAR(100) NOT NULL DEFAULT "pending"'
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
