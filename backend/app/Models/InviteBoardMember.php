<?php

namespace App\Models;
use Core\CRUD;

class InviteBoardMember extends CRUD {

    function __construct(){
        parent::__construct('board_invites');
    }
}
