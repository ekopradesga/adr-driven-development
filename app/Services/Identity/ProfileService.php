<?php

namespace App\Services\Identity;

use App\Models\User;

/**
 * ProfileService — Identity & Access module.
 *
 * Owns profile management business logic for authenticated users.
 */
class ProfileService
{
    /**
     * Update the authenticated user's profile.
     * Clears email verification timestamp if email is changed.
     */
    public function update(User $user, array $data): User
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}
