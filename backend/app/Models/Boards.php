<?php

namespace App\Models;
use Core\CRUD;
use Core\Logger;

class Boards extends CRUD {

    public function __construct(?Logger $logger = null) {
        parent::__construct('boards', $logger);
    }
}
