<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubJoinRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClubManagementController extends Controller
{
    public function index(Request $request): View
    {
        $isSuperAdmin = (bool) $request->user()?->isSuperAdmin();

        $clubIds = $isSuperAdmin
            ? Club::query()->pluck('id')
            : DB::table('club_user')
                ->where('user_id', $request->user()->getKey())
                ->whereIn('role', ['manager', 'owner', 'admin'])
                ->pluck('club_id');

        $clubs = Club::query()
            ->whereIn('id', $clubIds)
            ->orderBy('name')
            ->get();

        $joinRequests = ClubJoinRequest::query()
            ->whereIn('club_id', $clubIds)
            ->where('status', 'pending')
            ->with(['user', 'club'])
            ->orderByDesc('id')
            ->get();

        $members = DB::table('club_user')
            ->join('clubs', 'clubs.id', '=', 'club_user.club_id')
            ->join('users', 'users.id', '=', 'club_user.user_id')
            ->whereIn('club_user.club_id', $clubIds)
            ->select([
                'clubs.id as club_id',
                'clubs.name as club_name',
                'users.id as user_id',
                'users.name as user_name',
                'users.email',
                'club_user.role',
                'club_user.joined_at',
            ])
            ->orderBy('clubs.name')
            ->orderBy('users.name')
            ->get();

        return view('admin.clubs.index', [
            'clubs' => $clubs,
            'joinRequests' => $joinRequests,
            'members' => $members,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function approveJoinRequest(Request $request, ClubJoinRequest $clubJoinRequest): RedirectResponse
    {
        $club = $clubJoinRequest->club;

        if (! $club) {
            abort(404);
        }

        $this->authorize('manageMembers', $club);

        $club->users()->syncWithoutDetaching([
            $clubJoinRequest->user_id => [
                'role' => 'trainer',
                'joined_at' => now(),
            ],
        ]);

        $clubJoinRequest->update([
            'status' => 'approved',
            'reviewed_by_user_id' => $request->user()->getKey(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.clubs.index')->with('status', 'Beitrittsanfrage freigegeben.');
    }

    public function declineJoinRequest(Request $request, ClubJoinRequest $clubJoinRequest): RedirectResponse
    {
        $club = $clubJoinRequest->club;

        if (! $club) {
            abort(404);
        }

        $this->authorize('manageMembers', $club);

        $clubJoinRequest->update([
            'status' => 'declined',
            'reviewed_by_user_id' => $request->user()->getKey(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.clubs.index')->with('status', 'Beitrittsanfrage abgelehnt.');
    }
}
