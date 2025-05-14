<?php

namespace Tests\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Kirschbaum\Commentions\Contracts\Commenter;
use Tests\Database\Factories\UserFactory;

class User extends Model implements AuthenticatableContract, Commenter
{
    use Authenticatable;
    use Authorizable;
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): UserFactory
    {
        return new UserFactory();
    }
}
