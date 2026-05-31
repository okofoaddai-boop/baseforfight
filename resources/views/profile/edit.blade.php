<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Meine Daten') }} | BaseForFight</title>
    @include('partials.app-assets')
    <style>
        :root {
            --bg: #f4f6f2;
            --panel: #fafcf8;
            --line: #c8d4c2;
            --ink-soft: #4d6050;
            --green: #016734;
            --green-light: #7db928;
            --danger: #dd6850;
        }

        * { box-sizing: border-box; }
        body { background: var(--bg); }
        .page { width: min(960px, calc(100% - 24px)); margin: 0 auto; padding: 1rem 0 2rem; }
        .shell { display: grid; gap: 16px; }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 18px; padding: 22px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { display: grid; gap: 6px; }
        label { font-weight: 700; }
        input { width: 100%; border: 1px solid var(--line); border-radius: 10px; padding: 11px 12px; font: inherit; background: #fff; }
        .hint { color: var(--ink-soft); font-size: 14px; }
        .status { background: #eef7e9; border: 1px solid var(--green-light); color: var(--green); border-radius: 12px; padding: 10px 14px; }
        .errors { background: #fff0ed; border: 1px solid #f0b4ab; color: #8a2d1d; border-radius: 12px; padding: 10px 14px; }
        .actions { display: flex; justify-content: flex-end; }
        .btn { border: 0; border-radius: 999px; background: var(--green); color: #fff; font-weight: 700; padding: 11px 20px; cursor: pointer; }

        @media (max-width: 720px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="app-shell">
    @include('partials.main-navbar')

    <div class="page">
        <div class="shell">
            <section class="card">
                <div class="app-eyebrow mb-2">{{ __('Profil') }}</div>
                <h1 class="app-title mb-2">{{ __('Meine Daten') }}</h1>
                <p class="hint mb-0">{{ __('Hier kannst du deine Basisdaten für Login, Kommunikation und Vereinsverwaltung pflegen.') }}</p>
            </section>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="errors">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="card">
                <form method="post" action="{{ route('profile.update') }}" class="shell">
                    @csrf
                    @method('patch')

                    <div class="grid">
                        <div class="field">
                            <label for="first_name">{{ __('Vorname') }}</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        </div>
                        <div class="field">
                            <label for="last_name">{{ __('Nachname') }}</label>
                            <input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        </div>
                        <div class="field">
                            <label for="email">{{ __('E-Mail') }}</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="field">
                            <label for="phone">{{ __('Telefon') }}</label>
                            <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                        </div>
                    </div>

                    <div class="actions">
                        <button class="btn" type="submit">{{ __('Daten speichern') }}</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    @include('partials.main-footer')
</body>
</html>