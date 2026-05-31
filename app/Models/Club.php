<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'billing_company_name',
        'billing_contact_name',
        'billing_email',
        'billing_address_line1',
        'billing_address_line2',
        'billing_zip',
        'billing_city',
        'billing_country',
        'created_by_user_id',
        'is_demo',
        'demo_batch',
    ];

    protected $casts = [
        'is_demo' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ClubMembership::class);
    }

    public function fighters(): HasMany
    {
        return $this->hasMany(Fighter::class);
    }

    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_club_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ClubInvitation::class);
    }
}
