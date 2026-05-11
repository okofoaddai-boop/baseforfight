<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubInvitation;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\DB;
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
                ['method' => 'GET', 'path' => '/api/v1/fighters', 'description' => 'Kaempfer laden'],
                ['method' => 'POST', 'path' => '/api/v1/events', 'description' => 'Event anlegen'],
                ['method' => 'POST', 'path' => '/api/v1/events/{event}/cancel', 'description' => 'Event absagen'],
                ['method' => 'POST', 'path' => '/api/v1/registrations', 'description' => 'Einschreibung erstellen'],
                ['method' => 'PATCH', 'path' => '/api/v1/registrations/{registration}', 'description' => 'Status aktualisieren'],
            ],
        ];

        $adminClubs = DB::table('club_user')
            ->join('clubs', 'clubs.id', '=', 'club_user.club_id')
            ->where('club_user.user_id', auth()->id())
            ->whereIn('club_user.role', ['manager', 'owner', 'admin'])
            ->select('clubs.id', 'clubs.name', 'clubs.slug', 'club_user.role')
            ->orderBy('clubs.name')
            ->get();

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
            ->with(['clubs' => function ($query): void {
                $query->orderBy('clubs.name');
            }])
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
