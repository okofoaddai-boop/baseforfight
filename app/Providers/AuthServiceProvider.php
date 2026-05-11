<?php

namespace App\Providers;

use App\Models\Club;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use App\Policies\ClubPolicy;
use App\Policies\EventPolicy;
use App\Policies\FighterPolicy;
use App\Policies\RegistrationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Club::class => ClubPolicy::class,
        Event::class => EventPolicy::class,
        Fighter::class => FighterPolicy::class,
        Registration::class => RegistrationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
