<?php

namespace database\Migrations;

use Core\CreateTable;

class addedColumnPostionInStatuses extends CreateTable
{
    public function __construct()
    {
        parent::__construct('statuses');
    }

    public function up(): void
    {
        $this->addColumn('position int ');
    }

    public function down(): void
    {
        return;
    }
}