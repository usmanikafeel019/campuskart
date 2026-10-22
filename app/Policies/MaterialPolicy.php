<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function create(User $u): bool
    {
        return $u->role === 'student';
    }

    public function update(User $u, Material $m): bool
    {
        return $u->role === 'student' && $u->id === $m->user_id;
    }

    public function delete(User $u, Material $m): bool
    {
        return $u->role === 'admin' || $u->id === $m->user_id;
    }
}
