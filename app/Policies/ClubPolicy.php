<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\ClubMembershipRole;
use App\Models\User;
use App\Services\ClubPermissionService;

class ClubPolicy
{
    public function __construct(private readonly ClubPermissionService $permissions)
    {
    }

    public function view(User $user, Club $club): bool
    {
        return $user->isPlatformAdmin() || $user->isMemberOf((int) $club->getKey());
    }

    public function update(User $user, Club $club): bool
    {
        return $this->permissions->canManageClub($user, (int) $club->getKey());
    }

    public function manageMembers(User $user, Club $club): bool
    {
        return $this->permissions->canManageClub($user, (int) $club->getKey());
    }

    public function assignRole(User $user, Club $club, string $targetRole): bool
    {
        if ($user->isPlatformAdmin()) {
            return in_array($targetRole, ClubMembershipRole::ALL_ROLES, true);
        }

        return $this->permissions->canManageClub($user, (int) $club->getKey())
            && in_array($targetRole, ClubMembershipRole::ALL_ROLES, true);
    }
}
