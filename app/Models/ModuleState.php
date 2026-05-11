<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleState extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'display_name',
        'is_active',
        'activated_at',
        'deactivated_at',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'meta' => 'array',
    ];
}
