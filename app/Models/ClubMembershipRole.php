<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubMembershipRole extends Model
{
    public const ROLE_CLUB_MANAGER = 'club_manager';
    public const ROLE_EVENT_MANAGER = 'event_manager';
    public const ROLE_TRAINER = 'trainer';

    public const ALL_ROLES = [
        self::ROLE_CLUB_MANAGER,
        self::ROLE_EVENT_MANAGER,
        self::ROLE_TRAINER,
    ];

    public const ROLE_LABELS = [
        self::ROLE_CLUB_MANAGER  => 'Club-Manager',
        self::ROLE_EVENT_MANAGER => 'Veranstaltungsmanager',
        self::ROLE_TRAINER       => 'Trainer',
    ];

    protected $fillable = [
        'club_membership_id',
        'role',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(ClubMembership::class, 'club_membership_id');
    }
}
