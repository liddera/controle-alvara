<?php

namespace App\Policies;

use App\Models\AlertConfig;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AlertConfigPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AlertConfig $alertConfig): bool
    {
        return $user->id === $alertConfig->user_id;
    }
}
