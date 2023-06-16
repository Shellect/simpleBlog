<?php

namespace app\models;

use engine\Model;

class User extends Model
{

    public function fieldsTable()
    {
        return ['login', 'password'];
    }
}