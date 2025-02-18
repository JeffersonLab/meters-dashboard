<?php

namespace App\Policies;

use App\Models\Meters\MeterLimit;
use Illuminate\Auth\Access\Response;
use Jlab\Auth\User;

class MeterLimitPolicy
{
    protected function isAdminUser(User $user) : bool{
        return in_array($user->username, config('auth.admin_usernames',[]));
    }

    /**
     * Pre-authorization check allows admin users to perform all actions.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($this->isAdminUser($user)) {
            return true;
        }
        return null;
    }

    /**
     * Determine if the given post can be updated by the user.
     */
    public function create(User $user): Response
    {
        return $this->isAdminUser($user)
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    /**
     * Determine if the given post can be updated by the user.
     */
    public function update(User $user, MeterLimit $meterLimit): Response
    {
        return $this->isAdminUser($user)
            ? Response::allow()
            : Response::denyWithStatus(403);
    }

    /**
     * Determine if the given post can be updated by the user.
     */
    public function delete(User $user, MeterLimit $meterLimit): Response
    {
        return $this->isAdminUser($user)
            ? Response::allow()
            : Response::denyWithStatus(403);
    }
}
