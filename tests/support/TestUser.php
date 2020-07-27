<?php

namespace Laracasts\Cypress\Tests\Support;

use Illuminate\Foundation\Auth\User as Authenticatable;

class TestUser extends Authenticatable
{
    protected $table = 'users';
}
