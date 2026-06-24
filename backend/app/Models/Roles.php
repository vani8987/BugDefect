<?php

namespace App\Models;
use Core\CRUD;

class Roles extends CRUD {

    function __construct(){
        return parent::__construct('roles');
    }
}
