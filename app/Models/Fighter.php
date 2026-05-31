<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fighter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'club_id',
        'created_by_user_id',
        'first_name',
        'last_name',
        'birth_date',
        'sex',
        'weight_class',
        'sport_modules',
        'boxing_weight_entries',
        'boxing_bout_count_entries',
        'boxing_pass_entries',
        'status',
        'is_demo',
        'demo_batch',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'sport_modules' => 'array',
        'boxing_weight_entries' => 'array',
        'boxing_bout_count_entries' => 'array',
        'boxing_pass_entries' => 'array',
        'is_demo' => 'boolean',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
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
