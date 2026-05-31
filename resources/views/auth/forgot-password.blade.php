<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Passwort vergessen') }} | BaseForFight</title>
    @include('partials.app-assets')
</head>
<body class="app-shell">
    @include('partials.main-navbar')

    <main class="app-page auth-stage">
        <div class="auth-card card border-0">
            <div class="card-body">
                <form action="{{ route('password.email') }}" method="post">
                    @csrf
                    <div class="app-eyebrow mb-2">{{ __('Passwort-Hilfe') }}</div>
                    <h1 class="app-title h2 mb-2">{{ __('Passwort vergessen') }}</h1>
                    <p class="text-secondary mb-4">{{ __('Gib deine E-Mail-Adresse ein. Wir senden dir einen Link, mit dem du dein Passwort neu setzen kannst.') }}</p>

                    @if (session('status'))
                        <div class="flash-card success mb-3">{{ session('status') }}</div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="email">{{ __('E-Mail-Adresse') }}</label>
                        <input class="form-control form-control-lg" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <button class="btn btn-success btn-lg w-100 rounded-pill" type="submit">{{ __('Reset-Link senden') }}</button>
                </form>

                <div class="d-grid gap-2 mt-4">
                    <a class="link-success fw-semibold text-decoration-none" href="{{ route('login') }}">{{ __('Zurück zum Login') }}</a>
                    <a class="link-success fw-semibold text-decoration-none" href="{{ route('register') }}">{{ __('Konto erstellen') }}</a>
                </div>
            </div>
        </div>
    </main>

    @include('partials.main-footer')
    @include('partials.app-scripts')
</body>
</html>