<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubJoinRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'user_id',
        'requested_club_name',
        'requested_club_slug',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'review_note',
        'is_demo',
        'demo_batch',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'is_demo' => 'boolean',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
