<?php

namespace App\Policies;

use App\Models\Fighter;
use App\Models\User;

class FighterPolicy
{
    public function view(User $user, Fighter $fighter): bool
    {
        return $user->clubRoleFor((int) $fighter->getAttribute('club_id')) !== null;
    }

    public function createForClub(User $user, int $clubId): bool
    {
        return in_array($user->clubRoleFor($clubId), ['manager', 'owner', 'admin', 'trainer', 'coach'], true);
    }

    public function update(User $user, Fighter $fighter): bool
    {
        return in_array(
            $user->clubRoleFor((int) $fighter->getAttribute('club_id')),
            ['manager', 'owner', 'admin', 'trainer', 'coach'],
            true
        );
    }
}
