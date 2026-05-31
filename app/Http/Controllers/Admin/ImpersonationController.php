<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function switchTo(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        if (! $request->session()->has('impersonator_id')) {
            $request->session()->put('impersonator_id', $request->user()->getKey());
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('welcome')->with('status', 'Ansicht gewechselt.');
    }

    public function switchToClubRole(Request $request, Club $club, string $role): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $requestedRole = strtolower(trim($role));
        if (! in_array($requestedRole, ['manager', 'trainer'], true)) {
            abort(404);
        }

        $rolePriority = $requestedRole === 'manager'
            ? ['manager', 'owner', 'admin']
            : ['trainer', 'coach'];

        $candidate = $club->users()
            ->wherePivotIn('role', $rolePriority)
            ->get()
            ->sortBy(function (User $user) use ($rolePriority): int {
                $pivotRole = (string) ($user->pivot->role ?? '');
                $priority = array_search($pivotRole, $rolePriority, true);

                return is_int($priority) ? $priority : 999;
            })
            ->first();

        if (! $candidate instanceof User) {
            return redirect()
                ->route('admin.clubs.index')
                ->with('status', 'Keine passende Nutzerrolle im Verein gefunden.');
        }

        if (! $request->session()->has('impersonator_id')) {
            $request->session()->put('impersonator_id', $request->user()->getKey());
        }

        Auth::login($candidate);
        $request->session()->regenerate();

        return redirect()->route('welcome')->with('status', 'Ansicht gewechselt: ' . $club->name . ' als ' . $requestedRole . '.');
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->pull('impersonator_id');

        if (! $impersonatorId) {
            return redirect()->route('welcome');
        }

        Auth::loginUsingId((int) $impersonatorId);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('status', 'Zur Superuser-Ansicht zurückgekehrt.');
    }
}
