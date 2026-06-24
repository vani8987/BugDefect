<?php

namespace database\Migrations;

use Core\CreateTable;

class AddedColumnDescription extends CreateTable
{
    public function __construct()
    {
        parent::__construct('boards');
    }

    public function up(): void
    {
        $this->addColumn('description VARCHAR(255)');
    }

    public function down(): void
    {
        return;
    }
}