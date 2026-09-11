<?php

namespace Tests\Models;

class UserWithCommenterName extends User
{
    protected $table = 'users';

    public function getCommenterName(): string
    {
        return 'Commenter #' . $this->id;
    }
}
