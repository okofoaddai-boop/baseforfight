<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'registration_deadline',
        'max_registrations',
        'allow_waitlist',
        'entry_fee_cents',
        'currency',
        'info_documents',
        'location',
        'sport_module',
        'venue_name',
        'address_line1',
        'address_line2',
        'postal_code',
        'city',
        'country',
        'boxing_package_key',
        'boxing_age_classes',
        'boxing_sexes',
        'boxing_performance_classes',
        'status',
        'published_at',
        'organizer_club_id',
        'created_by_user_id',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'registration_deadline' => 'datetime',
        'max_registrations' => 'integer',
        'allow_waitlist' => 'boolean',
        'entry_fee_cents' => 'integer',
        'info_documents' => 'array',
        'boxing_age_classes' => 'array',
        'boxing_sexes' => 'array',
        'boxing_performance_classes' => 'array',
        'published_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function organizerClub(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'organizer_club_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
