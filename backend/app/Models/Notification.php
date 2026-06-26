<?php

namespace App\Models;
use Core\CRUD;

class Notification extends CRUD {

    function __construct(){
        parent::__construct('notification');
    }
}
