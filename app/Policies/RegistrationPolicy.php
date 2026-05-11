<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegistrationPolicy
{
    public function view(User $user, Registration $registration): bool
    {
        $clubId = DB::table('fighters')
            ->where('id', $registration->getAttribute('fighter_id'))
            ->value('club_id');

        if (! is_numeric($clubId)) {
            return false;
        }

        return $user->clubRoleFor((int) $clubId) !== null;
    }

    public function createForFighterClub(User $user, int $clubId): bool
    {
        return in_array($user->clubRoleFor($clubId), ['manager', 'owner', 'admin', 'trainer', 'coach'], true);
    }

    public function update(User $user, Registration $registration): bool
    {
        $clubId = DB::table('fighters')
            ->where('id', $registration->getAttribute('fighter_id'))
            ->value('club_id');

        if (! is_numeric($clubId)) {
            return false;
        }

        return in_array(
            $user->clubRoleFor((int) $clubId),
            ['manager', 'owner', 'admin', 'trainer', 'coach'],
            true
        );
    }

    public function delete(User $user, Registration $registration): bool
    {
        return $this->update($user, $registration);
    }
}
