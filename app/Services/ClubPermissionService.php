<?php

namespace App\Services;

use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Models\User;

class ClubPermissionService
{
    public function canManageClub(User $user, int $clubId): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        return $user->hasClubRole($clubId, ClubMembershipRole::ROLE_CLUB_MANAGER);
    }

    public function canManageEvents(User $user, int $clubId): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        return $user->hasClubRole($clubId, [
            ClubMembershipRole::ROLE_EVENT_MANAGER,
            ClubMembershipRole::ROLE_CLUB_MANAGER,
        ]);
    }

    public function canManageAthletes(User $user, int $clubId): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        return $user->hasClubRole($clubId, [
            ClubMembershipRole::ROLE_TRAINER,
            ClubMembershipRole::ROLE_CLUB_MANAGER,
        ]);
    }

    public function isMember(User $user, int $clubId): bool
    {
        return $user->isMemberOf($clubId);
    }

    public function addMembership(User $user, int $clubId, array $roles): ClubMembership
    {
        $membership = ClubMembership::query()->firstOrCreate(
            ['club_id' => $clubId, 'user_id' => $user->getKey()],
            ['joined_at' => now()],
        );

        foreach ($roles as $role) {
            ClubMembershipRole::query()->firstOrCreate([
                'club_membership_id' => $membership->getKey(),
                'role' => $role,
            ]);
        }

        return $membership;
    }

    public function removeMembership(User $user, int $clubId): void
    {
        ClubMembership::query()
            ->where('club_id', $clubId)
            ->where('user_id', $user->getKey())
            ->delete();
    }

    public function syncRoles(User $user, int $clubId, array $roles): void
    {
        $membership = ClubMembership::query()
            ->where('club_id', $clubId)
            ->where('user_id', $user->getKey())
            ->firstOrFail();

        ClubMembershipRole::query()
            ->where('club_membership_id', $membership->getKey())
            ->delete();

        foreach ($roles as $role) {
            ClubMembershipRole::query()->create([
                'club_membership_id' => $membership->getKey(),
                'role' => $role,
            ]);
        }
    }

    public function addRole(User $user, int $clubId, string $role): void
    {
        $membership = ClubMembership::query()
            ->where('club_id', $clubId)
            ->where('user_id', $user->getKey())
            ->firstOrFail();

        ClubMembershipRole::query()->firstOrCreate([
            'club_membership_id' => $membership->getKey(),
            'role' => $role,
        ]);
    }

    public function removeRole(User $user, int $clubId, string $role): void
    {
        $membership = ClubMembership::query()
            ->where('club_id', $clubId)
            ->where('user_id', $user->getKey())
            ->first();

        if (! $membership) {
            return;
        }

        ClubMembershipRole::query()
            ->where('club_membership_id', $membership->getKey())
            ->where('role', $role)
            ->delete();
    }

    public function hasClubManager(int $clubId): bool
    {
        return ClubMembershipRole::query()
            ->whereHas('membership', fn ($q) => $q->where('club_id', $clubId))
            ->where('role', ClubMembershipRole::ROLE_CLUB_MANAGER)
            ->exists();
    }

    public function countClubManagers(int $clubId): int
    {
        return (int) ClubMembershipRole::query()
            ->whereHas('membership', fn ($q) => $q->where('club_id', $clubId))
            ->where('role', ClubMembershipRole::ROLE_CLUB_MANAGER)
            ->count();
    }
}
