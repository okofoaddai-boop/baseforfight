<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubInvitation;
use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View|Factory
    {
        $currentUser = auth()->user();
        $isSuperAdmin = (bool) $currentUser?->isSuperAdmin();

        $stats = [
            'users' => User::query()->count(),
            'clubs' => Club::query()->count(),
            'fighters' => Fighter::query()->count(),
            'events' => Event::query()->count(),
            'registrations' => Registration::query()->count(),
            'open_invitations' => ClubInvitation::query()->whereNull('accepted_at')->count(),
        ];

        $endpointGroups = [
            'Auth' => [
                ['method' => 'POST', 'path' => '/api/v1/auth/token', 'description' => 'Token ausstellen'],
                ['method' => 'DELETE', 'path' => '/api/v1/auth/token', 'description' => 'Aktuelles Token widerrufen'],
                ['method' => 'GET', 'path' => '/api/v1/me', 'description' => 'Profil des eingeloggten Users'],
            ],
            'Clubs' => [
                ['method' => 'GET', 'path' => '/api/v1/clubs', 'description' => 'Eigene Clubs laden'],
                ['method' => 'POST', 'path' => '/api/v1/clubs', 'description' => 'Club anlegen'],
                ['method' => 'PATCH', 'path' => '/api/v1/clubs/{club}', 'description' => 'Club aktualisieren'],
                ['method' => 'POST', 'path' => '/api/v1/clubs/{club}/invitations', 'description' => 'Mitglied einladen'],
                ['method' => 'POST', 'path' => '/api/v1/clubs/invitations/accept', 'description' => 'Einladung annehmen'],
            ],
            'Sportbetrieb' => [
                ['method' => 'GET', 'path' => '/api/v1/fighters', 'description' => 'Kämpfer laden'],
                ['method' => 'POST', 'path' => '/api/v1/events', 'description' => 'Event anlegen'],
                ['method' => 'POST', 'path' => '/api/v1/events/{event}/cancel', 'description' => 'Event absagen'],
                ['method' => 'POST', 'path' => '/api/v1/registrations', 'description' => 'Einschreibung erstellen'],
                ['method' => 'PATCH', 'path' => '/api/v1/registrations/{registration}', 'description' => 'Status aktualisieren'],
            ],
        ];

        $adminRoles = [
            ClubMembershipRole::ROLE_CLUB_MANAGER,
            ClubMembershipRole::ROLE_EVENT_MANAGER,
        ];

        $adminClubs = collect();

        if ($currentUser) {
            $adminClubs = ClubMembership::query()
                ->with(['club:id,name,slug', 'roles'])
                ->where('user_id', $currentUser->getKey())
                ->whereHas('roles', fn ($query) => $query->whereIn('role', $adminRoles))
                ->get()
                ->flatMap(function (ClubMembership $membership) use ($adminRoles) {
                    $club = $membership->club;

                    if (! $club) {
                        return [];
                    }

                    return $membership->roles
                        ->whereIn('role', $adminRoles)
                        ->map(fn (ClubMembershipRole $role) => (object) [
                            'id' => $club->getKey(),
                            'name' => $club->name,
                            'slug' => $club->slug,
                            'role' => $role->role,
                        ]);
                })
                ->sortBy(['name', 'role'])
                ->values();
        }

        $dashboardClubs = $isSuperAdmin
            ? Club::query()
                ->withCount('fighters')
                ->orderBy('name')
                ->get(['id', 'name', 'slug'])
            : Club::query()
                ->whereIn('id', $adminClubs->pluck('id')->all())
                ->withCount('fighters')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);

        $switchableUsers = User::query()
            ->with([
                'memberships.club:id,name,slug',
                'memberships.roles',
            ])
            ->orderBy('name')
            ->when(! $isSuperAdmin, fn ($query) => $query->limit(20))
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'endpointGroups' => $endpointGroups,
            'adminClubs' => $adminClubs,
            'dashboardClubs' => $dashboardClubs,
            'switchableUsers' => $switchableUsers,
            'isImpersonating' => session()->has('impersonator_id'),
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}
