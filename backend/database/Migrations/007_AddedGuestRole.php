<?php
namespace database\Migrations;
use Core\CRUD;
use RuntimeException;

class AddedGuestRole extends CRUD {
    public function __construct()
    {
        parent::__construct('roles');
    }

    public function up(): void {
        $guestRoleName = $_ENV['BOARD_ROLE_GUEST'] ?? getenv('BOARD_ROLE_GUEST') ?: 'guest';
        $created = $this->create(['id', 'name'], [1, $guestRoleName]);

        if (!$created) {
            throw new RuntimeException("Unable to add the {$guestRoleName} role.");
        }
    }

    public function down(): void
    {
        return;
    }
}