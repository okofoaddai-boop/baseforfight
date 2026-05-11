<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        $clubId = $event->getAttribute('organizer_club_id');

        if (is_numeric($clubId) && $user->clubRoleFor((int) $clubId) !== null) {
            return true;
        }

        return (int) $event->getAttribute('created_by_user_id') === (int) $user->getKey();
    }

    public function createForClub(User $user, int $clubId): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        return in_array($user->clubRoleFor($clubId), ['manager', 'owner', 'admin'], true);
    }

    public function update(User $user, Event $event): bool
    {
        $clubId = $event->getAttribute('organizer_club_id');

        return is_numeric($clubId)
            && (
                $user->isPlatformAdmin()
                || in_array($user->clubRoleFor((int) $clubId), ['manager', 'owner', 'admin'], true)
            );
    }

    public function cancel(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
