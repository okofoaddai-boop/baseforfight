<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Services\ClubPermissionService;

class EventPolicy
{
    public function __construct(private readonly ClubPermissionService $permissions)
    {
    }

    public function view(User $user, Event $event): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        $clubId = $event->getAttribute('organizer_club_id');

        if (is_numeric($clubId) && $user->isMemberOf((int) $clubId)) {
            return true;
        }

        return (int) $event->getAttribute('created_by_user_id') === (int) $user->getKey();
    }

    public function createForClub(User $user, int $clubId): bool
    {
        return $this->permissions->canManageEvents($user, $clubId);
    }

    public function update(User $user, Event $event): bool
    {
        $clubId = $event->getAttribute('organizer_club_id');

        return is_numeric($clubId)
            && $this->permissions->canManageEvents($user, (int) $clubId);
    }

    public function cancel(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }
}
