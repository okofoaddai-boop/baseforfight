<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    public function view(User $user, Club $club): bool
    {
        return $user->isPlatformAdmin() || $user->clubRoleFor((int) $club->getKey()) !== null;
    }

    public function update(User $user, Club $club): bool
    {
        return in_array($user->clubRoleFor((int) $club->getKey()), ['manager', 'owner', 'admin'], true);
    }

    public function manageMembers(User $user, Club $club): bool
    {
        return in_array($user->clubRoleFor((int) $club->getKey()), ['manager', 'owner', 'admin'], true);
    }

    public function assignRole(User $user, Club $club, string $targetRole): bool
    {
        $actorRole = $user->clubRoleFor((int) $club->getKey());

        if (in_array($actorRole, ['manager', 'owner'], true)) {
            return in_array($targetRole, ['manager', 'owner', 'admin', 'trainer', 'coach', 'member'], true);
        }

        if ($actorRole === 'admin') {
            return in_array($targetRole, ['trainer', 'coach', 'member'], true);
        }

        return false;
    }
}
