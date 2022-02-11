<?php

namespace Laracasts\Cypress\Tests\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laracasts\Cypress\Tests\Database\Factories\UserFactory;

class TestUser extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';
    protected $hidden = ['plan'];

    protected static function booted()
    {
        static::created(function ($user) {
            $user->profile()->create(['location' => 'USA']);
        });
    }

    public function profile()
    {
        return $this->hasOne(TestProfile::class, 'user_id');
    }

    protected static function newFactory()
    {
        return new UserFactory();
    }
}
