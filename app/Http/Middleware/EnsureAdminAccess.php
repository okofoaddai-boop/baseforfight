<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $isAdmin = DB::table('club_user')
            ->where('user_id', $user->getKey())
            ->whereIn('role', ['manager', 'admin'])
            ->exists();

        if (! $isAdmin) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
