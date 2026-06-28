<?php

namespace database\Migrations;

use Core\CRUD;
use RuntimeException;

class AddedBoardRoles extends CRUD
{
    public function __construct()
    {
        parent::__construct('roles');
    }

    public function up(): void
    {
        $roles = [
            $_ENV['BOARD_ROLE_ADMIN'] ?? getenv('BOARD_ROLE_ADMIN') ?: 'admin',
            $_ENV['BOARD_ROLE_DEVELOPER'] ?? getenv('BOARD_ROLE_DEVELOPER') ?: 'developer',
        ];

        foreach ($roles as $role) {
            if (!$this->create(['name'], [$role])) {
                throw new RuntimeException("Unable to add the {$role} role.");
            }
        }
    }

    public function down(): void
    {
        return;
    }
}
