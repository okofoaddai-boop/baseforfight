<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'club_name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $clubName = trim($validated['club_name']);
        $clubSlug = Str::slug($clubName);

        if ($clubSlug === '') {
            return back()->withInput()->withErrors([
                'club_name' => 'Der Vereinsname ist ungueltig.',
            ]);
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'password' => Hash::make($validated['password']),
            'email_verification_token' => Str::random(64),
            'is_admin_support' => false,
            'is_super_admin' => false,
        ]);

        $matchedClub = Club::query()->where('slug', $clubSlug)->first();

        if (! $matchedClub) {
            $matchedClub = $this->findLikelyClubMatch($clubName);
        }

        $createdManagerClub = false;

        if ($matchedClub) {
            ClubJoinRequest::query()->create([
                'club_id' => $matchedClub->getKey(),
                'user_id' => $user->getKey(),
                'requested_club_name' => $clubName,
                'requested_club_slug' => $clubSlug,
                'status' => 'pending',
            ]);
        } else {
            $club = Club::query()->create([
                'name' => $clubName,
                'slug' => $clubSlug . '-' . Str::lower(Str::random(4)),
                'created_by_user_id' => $user->getKey(),
            ]);

            $club->users()->attach($user->getKey(), [
                'role' => 'manager',
                'joined_at' => now(),
            ]);

            $createdManagerClub = true;
        }

        $verificationUrl = route('register.verify-email', [
            'token' => $user->getAttribute('email_verification_token'),
        ]);

        $mailError = false;

        try {
            Mail::raw(
                "Bitte bestaetige deine Registrierung bei BaseForFight:\n\n{$verificationUrl}",
                static function ($message) use ($user): void {
                    $message->to((string) $user->getAttribute('email'))
                        ->subject('BaseForFight: Bitte E-Mail bestaetigen');
                }
            );
        } catch (Throwable $exception) {
            $mailError = true;
            Log::warning('Registration verification mail could not be sent.', [
                'user_id' => $user->getKey(),
                'email' => $user->getAttribute('email'),
                'error' => $exception->getMessage(),
            ]);
        }

        $status = $createdManagerClub
            ? 'Registrierung erfolgreich. Bitte bestaetige deine E-Mail. Dein Verein wurde neu angelegt und du bist als Manager vorgemerkt.'
            : 'Registrierung erfolgreich. Bitte bestaetige deine E-Mail. Die Vereinsanfrage wurde an das Manager-Team weitergeleitet.';

        if ($mailError) {
            $status .= ' Hinweis: E-Mail-Versand war lokal nicht erreichbar. Bitte Mail-Konfiguration pruefen.';
        }

        return redirect()->route('register')->with('status', $status);
    }

    public function verifyEmail(string $token): RedirectResponse
    {
        $user = User::query()
            ->where('email_verification_token', $token)
            ->first();

        if (! $user) {
            return redirect()->route('register')->withErrors([
                'email' => 'Der Verifizierungslink ist ungueltig oder abgelaufen.',
            ]);
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);

        return redirect()->route('login')->with('status', 'E-Mail bestaetigt. Du kannst dich jetzt einloggen.');
    }

    private function findLikelyClubMatch(string $clubName): ?Club
    {
        $needle = Str::lower(trim($clubName));
        $clubs = Club::query()->select(['id', 'name', 'slug'])->get();

        foreach ($clubs as $club) {
            $candidate = Str::lower((string) $club->getAttribute('name'));

            if ($candidate === $needle || levenshtein($needle, $candidate) <= 2) {
                return $club;
            }

            if (str_contains($candidate, $needle) || str_contains($needle, $candidate)) {
                return $club;
            }
        }

        return null;
    }
}
