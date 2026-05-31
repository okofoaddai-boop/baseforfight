<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Anmelden') }} | BaseForFight</title>
    @include('partials.app-assets')
</head>
<body class="app-shell">
    @include('partials.main-navbar')

    <main class="app-page auth-stage">
        <div class="auth-card card border-0">
            <div class="card-body">
                <form action="{{ route('login.submit') }}" method="post">
                    @csrf
                    <div class="app-eyebrow mb-2">{{ __('Willkommen zurück') }}</div>
                    <h1 class="app-title h2 mb-2">{{ __('Anmelden') }}</h1>
                    <p class="text-secondary mb-4"><img class="auth-illustration" src="{{ asset('assets/brand/icons/icon_pass.png') }}" alt="{{ __('Boxer mit Zugangspass') }}">{{ __('Ein Login für Trainer, Manager, Admins und SuperUser.') }}</p>

                    @if (session('status'))
                        <div class="flash-card success mb-3">{{ session('status') }}</div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="email">{{ __('E-Mail') }}</label>
                        <input class="form-control form-control-lg" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">{{ __('Passwort') }}</label>
                        <input class="form-control form-control-lg" id="password" type="password" name="password" required>
                    </div>

                    @if ($errors->any())
                        <div class="flash-card error mb-3">{{ $errors->first() }}</div>
                    @endif

                    <button class="btn btn-success btn-lg w-100 rounded-pill" type="submit">{{ __('Einloggen') }}</button>

                    <div class="d-grid gap-2 mt-4">
                        <a class="link-success fw-semibold text-decoration-none" href="{{ route('password.request') }}">{{ __('Passwort vergessen?') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    @include('partials.main-footer')
    @include('partials.app-scripts')
</body>
</html>
