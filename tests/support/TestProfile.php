<?php

namespace Laracasts\Cypress\Tests\Support;

use Illuminate\Database\Eloquent\Model;

class TestProfile extends Model
{
    protected $table = 'profiles';

    protected $guarded = [];
}
