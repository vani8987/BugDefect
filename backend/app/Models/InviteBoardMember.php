<?php

namespace App\Models;
use Core\CRUD;
use Core\Logger;

class InviteBoardMember extends CRUD {

    public function __construct(?Logger $logger = null) {
        parent::__construct('board_invites', $logger);
    }
}
