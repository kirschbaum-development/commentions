<?php

namespace Tests\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Tests\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kirschbaum\FilamentComments\Contracts\CommentAuthor;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
    

class User extends Model implements CommentAuthor, AuthenticatableContract
{
    use HasFactory;
    use Authenticatable;

    protected $guarded = [];

    protected static function newFactory(): UserFactory
    {
        return new UserFactory;
    }
}