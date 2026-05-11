<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verification_token',
        'is_admin_support',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin_support' => 'boolean',
        'is_super_admin' => 'boolean',
    ];

    public function isAdminSupport(): bool
    {
        if ($this->isSuperAdminEmail()) {
            return true;
        }

        return (bool) $this->getAttribute('is_admin_support');
    }

    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdminEmail() || (bool) $this->getAttribute('is_super_admin');
    }

    public function isPlatformAdmin(): bool
    {
        return $this->isAdminSupport() || $this->isSuperAdmin();
    }

    public function isSuperAdminEmail(): bool
    {
        $email = strtolower((string) $this->getAttribute('email'));
        $emails = config('baseforfight.superuser_emails', []);

        return in_array($email, $emails, true);
    }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class)
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function createdClubs(): HasMany
    {
        return $this->hasMany(Club::class, 'created_by_user_id');
    }

    public function createdFighters(): HasMany
    {
        return $this->hasMany(Fighter::class, 'created_by_user_id');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by_user_id');
    }

    public function registrationsMade(): HasMany
    {
        return $this->hasMany(Registration::class, 'registered_by_user_id');
    }

    public function clubRoleFor(int $clubId): ?string
    {
        $role = DB::table('club_user')
            ->where('club_id', $clubId)
            ->where('user_id', $this->getKey())
            ->value('role');

        return is_string($role) ? $role : null;
    }
}
