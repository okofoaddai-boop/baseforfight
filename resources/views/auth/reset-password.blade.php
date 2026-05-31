<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Neues Passwort setzen') }} | BaseForFight</title>
    @include('partials.app-assets')
</head>
<body class="app-shell">
    @include('partials.main-navbar')

    <main class="app-page auth-stage">
        <div class="auth-card card border-0">
            <div class="card-body">
                <form action="{{ route('password.update') }}" method="post">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="app-eyebrow mb-2">{{ __('Sicherer Zugang') }}</div>
                    <h1 class="app-title h2 mb-2">{{ __('Neues Passwort setzen') }}</h1>
                    <p class="text-secondary mb-4">{{ __('Lege ein neues Passwort für dein Konto fest.') }}</p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="email">{{ __('E-Mail-Adresse') }}</label>
                        <input class="form-control form-control-lg" id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus>
                        @error('email')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">{{ __('Neues Passwort') }}</label>
                        <input class="form-control form-control-lg" id="password" type="password" name="password" required>
                        @error('password')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password_confirmation">{{ __('Neues Passwort bestätigen') }}</label>
                        <input class="form-control form-control-lg" id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>

                    <button class="btn btn-success btn-lg w-100 rounded-pill" type="submit">{{ __('Passwort speichern') }}</button>
                </form>
            </div>
        </div>
    </main>

    @include('partials.main-footer')
    @include('partials.app-scripts')
</body>
</html>