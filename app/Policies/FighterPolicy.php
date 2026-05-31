<?php

namespace App\Policies;

use App\Models\Fighter;
use App\Models\User;
use App\Services\ClubPermissionService;

class FighterPolicy
{
    public function __construct(private readonly ClubPermissionService $permissions)
    {
    }

    public function view(User $user, Fighter $fighter): bool
    {
        return $user->isPlatformAdmin()
            || $user->isMemberOf((int) $fighter->getAttribute('club_id'));
    }

    public function createForClub(User $user, int $clubId): bool
    {
        return $this->permissions->canManageAthletes($user, $clubId);
    }

    public function update(User $user, Fighter $fighter): bool
    {
        return $this->permissions->canManageAthletes($user, (int) $fighter->getAttribute('club_id'));
    }

    public function delete(User $user, Fighter $fighter): bool
    {
        return $this->permissions->canManageAthletes($user, (int) $fighter->getAttribute('club_id'));
    }
}
