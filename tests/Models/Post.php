<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tests\Database\Factories\PostFactory;
use Kirschbaum\FilamentComments\HasComments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kirschbaum\FilamentComments\Contracts\Commentable;

class Post extends Model implements Commentable
{
    use HasFactory;
    use HasComments;

    protected $guarded = [];

    protected static function newFactory(): PostFactory
    {
        return new PostFactory;
    }
}