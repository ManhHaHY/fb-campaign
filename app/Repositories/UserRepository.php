<?php

namespace App\Repositories;


use App\Models\User;

class UserRepository extends User
{
    protected $table = 'users';
}