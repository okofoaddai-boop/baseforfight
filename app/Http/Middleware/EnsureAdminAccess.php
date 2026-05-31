<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ClubMembershipRole;
use App\Models\ClubMembership;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isPlatformAdmin()) {
            return $next($request);
        }

        $isClubManager = ClubMembershipRole::query()
            ->where('role', ClubMembershipRole::ROLE_CLUB_MANAGER)
            ->whereHas('membership', fn ($q) => $q->where('user_id', $user->getKey()))
            ->exists();

        if (! $isClubManager) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
