<?php

namespace database\Migrations;

use Core\CreateTable;

class CreateTableNotification extends CreateTable
{
    public function __construct()
    {
        parent::__construct('notification');
    }

    public function up(): void
    {
        $this->createTable([
            'id INT AUTO_INCREMENT PRIMARY KEY',
            "title VARCHAR(150) NOT NULL",
            "type VARCHAR(60) NOT NULL DEFAULT 'text'",
            'data JSON NULL',
            'description VARCHAR(255)',
            'created DATETIME DEFAULT CURRENT_TIMESTAMP',
            'is_read BOOL NOT NULL DEFAULT false',
            'user_id INT NOT NULL',
            'FOREIGN KEY (user_id) REFERENCES users (id)',
        ]);
    }

    public function down(): void
    {
        $this->dropTable();
    }
}
