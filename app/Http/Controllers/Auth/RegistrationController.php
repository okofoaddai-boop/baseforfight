<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            'first_name' => ['required', 'string', 'min:2', 'max:120'],
            'last_name'  => ['required', 'string', 'min:2', 'max:120'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => __('Diese E-Mail-Adresse ist bereits registriert.'),
        ]);

        $firstName = trim((string) $validated['first_name']);
        $lastName  = trim((string) $validated['last_name']);
        $fullName  = trim($firstName . ' ' . $lastName);

        $user = User::query()->create([
            'name'                     => $fullName,
            'first_name'               => $firstName,
            'last_name'                => $lastName,
            'email'                    => Str::lower($validated['email']),
            'password'                 => Hash::make($validated['password']),
            'email_verification_token' => Str::random(64),
            'is_admin_support'         => false,
            'is_super_admin'           => false,
        ]);

        $verificationUrl = route('register.verify-email', [
            'token' => $user->getAttribute('email_verification_token'),
        ]);

        $mailError = false;

        try {
            Mail::raw(
                __('Hallo :name,', ['name' => $firstName]) . "\n\n"
                . __('Bitte bestätige deine Registrierung bei BaseForFight:') . "\n\n"
                . $verificationUrl . "\n\n"
                . __('Nach der Bestätigung kannst du dich einloggen und unter "Meine Vereine" einem Verein beitreten oder einen neuen Verein anlegen.'),
                static function ($message) use ($user): void {
                    $message->to((string) $user->getAttribute('email'))
                        ->subject(__('BaseForFight: Bitte E-Mail bestätigen'));
                }
            );
        } catch (Throwable $exception) {
            $mailError = true;
            Log::warning('Registration verification mail could not be sent.', [
                'user_id' => $user->getKey(),
                'email'   => $user->getAttribute('email'),
                'error'   => $exception->getMessage(),
            ]);
        }

        $status = __('Registrierung erfolgreich! Bitte bestätige deine E-Mail-Adresse, um dich einloggen zu koennen.');

        if ($mailError) {
            $status .= ' ' . __('Hinweis: E-Mail-Versand war lokal nicht erreichbar. Bitte Mail-Konfiguration prüfen.');
        }

        return redirect()->route('login')->with('status', $status);
    }

    public function verifyEmail(string $token): RedirectResponse
    {
        $user = User::query()
            ->where('email_verification_token', $token)
            ->first();

        if (! $user) {
            return redirect()->route('register')->withErrors([
                'email' => __('Der Verifizierungslink ist ungültig oder abgelaufen.'),
            ]);
        }

        $user->forceFill([
            'email_verified_at'        => now(),
            'email_verification_token' => null,
        ])->save();

        return redirect()->route('login')->with('status', __('E-Mail bestätigt. Du kannst dich jetzt einloggen und unter "Meine Vereine" einem Verein beitreten.'));
    }

    public function resendVerificationMail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = Str::lower((string) $validated['email']);
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return redirect()->route('register')->with('status', __('Wenn ein passendes Konto existiert, wurde eine neue Verifizierungs-E-Mail versendet.'));
        }

        if ($user->email_verified_at) {
            return redirect()->route('login')->with('status', __('Diese E-Mail-Adresse ist bereits bestätigt. Du kannst dich einloggen.'));
        }

        if (! $user->email_verification_token) {
            $user->forceFill([
                'email_verification_token' => Str::random(64),
            ])->save();
        }

        $firstName = trim((string) ($user->first_name ?: $user->name ?: ''));
        $verificationUrl = route('register.verify-email', [
            'token' => $user->getAttribute('email_verification_token'),
        ]);

        $mailError = false;

        try {
            Mail::raw(
                __('Hallo :name,', ['name' => $firstName]) . "\n\n"
                . __('Bitte bestätige deine Registrierung bei BaseForFight:') . "\n\n"
                . $verificationUrl . "\n\n"
                . __('Nach der Bestätigung kannst du dich einloggen und unter "Meine Vereine" einem Verein beitreten oder einen neuen Verein anlegen.'),
                static function ($message) use ($user): void {
                    $message->to((string) $user->getAttribute('email'))
                        ->subject(__('BaseForFight: Bitte E-Mail bestätigen'));
                }
            );
        } catch (Throwable $exception) {
            $mailError = true;
            Log::warning('Resend verification mail failed.', [
                'user_id' => $user->getKey(),
                'email'   => $user->getAttribute('email'),
                'error'   => $exception->getMessage(),
            ]);
        }

        $status = $mailError
            ? __('Die Verifizierungs-E-Mail konnte nicht gesendet werden. Bitte prüfe die Mail-Konfiguration.')
            : __('Neue Verifizierungs-E-Mail wurde versendet.');

        return redirect()->route('register')->with('status', $status)->withInput([
            'email' => $email,
        ]);
    }
}


