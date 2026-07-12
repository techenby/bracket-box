<?php

namespace App\Policies;

use App\Models\Bracket;
use App\Models\User;

class BracketPolicy
{
    public function update(User $user, Bracket $bracket): bool
    {
        return $bracket->user_id === $user->id;
    }

    public function launch(User $user, Bracket $bracket): bool
    {
        return $bracket->user_id === $user->id;
    }
}
