<?php

namespace App\Policies;

use App\Models\Label;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LabelPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param Label $label
     * @return bool
     */
    public function delete(User $user, Label $label)
    {
        return $user->id === $label->user_id;
    }
}
