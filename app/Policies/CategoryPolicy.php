<?php

namespace App\Policies;

use App\Models\User;

class CategoryPolicy
{
    public function create(User $user): bool
    {
        // En esta etapa cualquier usuario autenticado puede crear, sin distinción por rol.
        return true;
    }
}
