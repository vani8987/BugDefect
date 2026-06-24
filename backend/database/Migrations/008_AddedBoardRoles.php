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
        $roles = ['admin', 'developer'];

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
