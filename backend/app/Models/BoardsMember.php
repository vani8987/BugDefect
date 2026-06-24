<?php

namespace App\Models;
use Core\CRUD;

class BoardsMember extends CRUD {

    function __construct(){
        parent::__construct('board_member');
    }
}
