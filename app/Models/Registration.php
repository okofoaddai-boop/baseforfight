<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public const BILLABLE_REASON_DEADLINE_ACTIVE = 'deadline_active';
    public const BILLABLE_REASON_POST_DEADLINE_APPROVAL = 'post_deadline_approval';

    protected $fillable = [
        'fighter_id',
        'event_id',
        'status',
        'registered_by_user_id',
        'notes',
        'fighter_snapshot',
        'billable_at',
        'billable_reason',
        'withdrawn_at',
        'status_changed_at',
    ];

    protected $casts = [
        'fighter_snapshot' => 'array',
        'billable_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'status_changed_at' => 'datetime',
    ];

    public function fighter(): BelongsTo
    {
        return $this->belongsTo(Fighter::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(RegistrationStatusHistory::class)->latest('id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isWithdrawn(): bool
    {
        return $this->status === self::STATUS_WITHDRAWN;
    }
}
