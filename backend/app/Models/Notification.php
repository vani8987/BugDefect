<?php

namespace App\Models;
use Core\CRUD;
use Core\Logger;

class Notification extends CRUD {

    public function __construct(?Logger $logger = null) {
        parent::__construct('notification', $logger);
    }
}
