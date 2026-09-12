<?php

namespace Tests\Models;

use Filament\Models\Contracts\HasName;

class UserWithFilamentName extends User implements HasName
{
    protected $table = 'users';

    public function getFilamentName(): string
    {
        return 'Filament #' . $this->id;
    }
}
