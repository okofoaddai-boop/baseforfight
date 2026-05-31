<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;
use App\Services\ClubPermissionService;
use Illuminate\Support\Facades\DB;

class RegistrationPolicy
{
    public function __construct(private readonly ClubPermissionService $permissions)
    {
    }

    public function view(User $user, Registration $registration): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        $clubId = DB::table('fighters')
            ->where('id', $registration->getAttribute('fighter_id'))
            ->value('club_id');

        if (! is_numeric($clubId)) {
            return false;
        }

        $organizerClubId = DB::table('events')
            ->where('id', $registration->getAttribute('event_id'))
            ->value('organizer_club_id');

        return $user->isMemberOf((int) $clubId)
            || (is_numeric($organizerClubId) && $this->permissions->canManageEvents($user, (int) $organizerClubId));
    }

    public function createForFighterClub(User $user, int $clubId): bool
    {
        return $this->permissions->canManageAthletes($user, $clubId)
            || $this->permissions->canManageEvents($user, $clubId);
    }

    public function update(User $user, Registration $registration): bool
    {
        $clubId = DB::table('fighters')
            ->where('id', $registration->getAttribute('fighter_id'))
            ->value('club_id');

        if (! is_numeric($clubId)) {
            return false;
        }

        $organizerClubId = DB::table('events')
            ->where('id', $registration->getAttribute('event_id'))
            ->value('organizer_club_id');

        return $this->permissions->canManageAthletes($user, (int) $clubId)
            || $this->permissions->canManageEvents($user, (int) $clubId)
            || (is_numeric($organizerClubId) && $this->permissions->canManageEvents($user, (int) $organizerClubId));
    }

    public function delete(User $user, Registration $registration): bool
    {
        return $this->update($user, $registration);
    }
}
