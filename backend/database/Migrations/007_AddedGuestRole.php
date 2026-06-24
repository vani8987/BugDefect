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
        $created = $this->create(['id', 'name'], [1, 'guest']);

        if (!$created) {
            throw new RuntimeException('Не удалось добавить роль guest.');
        }
    }

    public function down(): void
    {
        return;
    }
}
