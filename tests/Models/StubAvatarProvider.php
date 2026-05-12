<?php

namespace Tests\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class StubAvatarProvider
{
    public function get(Model|Authenticatable $user): string
    {
        return 'https://stub.test/avatar/' . urlencode($user->name);
    }
}
