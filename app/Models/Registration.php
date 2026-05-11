<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'fighter_id',
        'event_id',
        'status',
        'registered_by_user_id',
        'notes',
        'fighter_snapshot',
    ];

    protected $casts = [
        'fighter_snapshot' => 'array',
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
}
