<?php

namespace App\Models;
use Core\CRUD;

class Boards extends CRUD {

    function __construct(){
        parent::__construct('boards');
    }
}
