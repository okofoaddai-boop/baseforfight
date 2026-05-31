<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Konto erstellen') }} | BaseForFight</title>
    @include('partials.app-assets')
</head>
<body class="app-shell">
    @include('partials.main-navbar')

    <main class="app-page auth-stage">
        <div class="auth-card card border-0" style="width:min(620px, 100%);">
            <div class="card-body">
                <form action="{{ route('register.store') }}" method="post">
                    @csrf
                    <div class="app-eyebrow mb-2">{{ __('Neues Konto') }}</div>
                    <h1 class="app-title h2 mb-2">{{ __('Konto erstellen') }}</h1>
                    <p class="text-secondary mb-3">{{ __('Registriere dich bei BaseForFight. Nach der E-Mail-Bestätigung kannst du einem Verein beitreten oder einen neuen anlegen.') }}</p>

                    <div class="flash-card success mb-4">
                        <strong>{{ __('Schritt 1 von 2:') }}</strong> {{ __('Persönliche Daten & Passwort -> E-Mail bestätigen -> Verein verbinden') }}
                    </div>

                    @if (session('status'))
                        <div class="flash-card success mb-3">{{ session('status') }}</div>
                    @endif

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="first_name">{{ __('Vorname') }}</label>
                            <input class="form-control form-control-lg" id="first_name" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name">
                            @error('first_name')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" for="last_name">{{ __('Nachname') }}</label>
                            <input class="form-control form-control-lg" id="last_name" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
                            @error('last_name')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold" for="email">{{ __('E-Mail-Adresse') }}</label>
                        <input class="form-control form-control-lg" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                        @error('email')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        @if ($errors->first('email') === __('Diese E-Mail-Adresse ist bereits registriert.'))
                            <div class="mt-2">
                                <button type="submit" form="resend-verification-form" class="btn btn-link px-0 text-success fw-semibold text-decoration-none">{{ __('Verifizierungs-E-Mail erneut senden') }}</button>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold" for="password">{{ __('Passwort') }}</label>
                        <input class="form-control form-control-lg" id="password" type="password" name="password" required autocomplete="new-password">
                        @error('password')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold" for="password_confirmation">{{ __('Passwort wiederholen') }}</label>
                        <input class="form-control form-control-lg" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                    </div>

                    <button class="btn btn-success btn-lg w-100 rounded-pill mt-4" type="submit">{{ __('Konto erstellen') }}</button>

                    <div class="small text-secondary mt-4">{{ __('Nach der Registrierung erhältst du einen Bestätigungslink per E-Mail. Erst danach ist der Login möglich.') }}</div>
                    <div class="d-grid gap-2 mt-3">
                        <a class="link-success fw-semibold text-decoration-none" href="{{ route('login') }}">{{ __('Bereits registriert? Einloggen') }}</a>
                        <a class="link-success fw-semibold text-decoration-none" href="{{ route('welcome') }}">{{ __('Zurück zur Startseite') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <form id="resend-verification-form" action="{{ route('register.resend-verification') }}" method="post" style="display:none;">
        @csrf
        <input type="hidden" name="email" value="{{ old('email') }}">
    </form>

    @include('partials.main-footer')
    @include('partials.app-scripts')
</body>
</html>
