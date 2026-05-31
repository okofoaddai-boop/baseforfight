<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $hasPhoneColumn = Schema::hasColumn('users', 'phone');

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:120'],
            'last_name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'phone' => $hasPhoneColumn ? ['nullable', 'string', 'max:40'] : ['nullable'],
        ]);

        $firstName = trim((string) $validated['first_name']);
        $lastName = trim((string) $validated['last_name']);

        $payload = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => trim($firstName . ' ' . $lastName),
            'email' => strtolower((string) $validated['email']),
        ];

        if ($hasPhoneColumn) {
            $payload['phone'] = $validated['phone'] !== null ? trim((string) $validated['phone']) : null;
        }

        $user->forceFill($payload)->save();

        return redirect()->route('profile.edit')->with('status', __('Deine Profildaten wurden gespeichert.'));
    }
}