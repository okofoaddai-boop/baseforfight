<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Meine Vereine') }} | BaseForFight</title>
    @include('partials.app-assets')
    <style>
        :root {
            --bg: #f4f6f2;
            --bg-alt: #e8ede4;
            --panel: #fafcf8;
            --ink: #2d3a2e;
            --ink-soft: #4d6050;
            --line: #c8d4c2;
            --green: #016734;
            --green-light: #7db928;
            --danger: #dd6850;
            --warn-bg: #fff8e6;
            --warn-line: #e9c46a;
        }
        * { box-sizing: border-box; }
        .page { width: min(1480px, calc(100% - 24px)); margin: 0 auto; padding: 1rem 0 2rem; }
        .shell { display: grid; gap: 16px; }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 18px; padding: 20px; margin-bottom: 18px; }
        .card h2 { margin: 0 0 14px; font-size: 1.1rem; }
        .membership-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 12px; border: 1px solid var(--line); border-radius: 12px; background: var(--bg-alt); margin-bottom: 10px; flex-wrap: wrap; }
        .role-badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 999px; background: var(--green); color: #fff; margin-right: 4px; margin-top: 3px; }
        .role-badge.event_manager { background: #7db928; color: #1b2d1f; }
        .role-badge.trainer { background: #4d6050; }
        .request-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 12px; background: var(--panel); margin-bottom: 8px; flex-wrap: wrap; }
        .badge-status { font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 999px; }
        .badge-status.pending { background: #fff8e6; color: #b87a00; border: 1px solid #e9c46a; }
        .badge-status.approved { background: #eef7e9; color: var(--green); border: 1px solid var(--green-light); }
        .badge-status.declined, .badge-status.cancelled { background: #fdf1ef; color: var(--danger); border: 1px solid #f5b8b0; }
        label { display: block; margin: 10px 0 5px; font-weight: 700; font-size: 14px; }
        input, select { width: 100%; border: 1px solid var(--line); border-radius: 10px; padding: 10px 12px; font: inherit; background: #fff; }
        .btn { border: 0; border-radius: 999px; background: var(--green); color: #fff; font-weight: 700; padding: 10px 20px; cursor: pointer; font-size: 14px; white-space: nowrap; }
        .btn-secondary { background: var(--bg-alt); color: var(--ink); border: 1px solid var(--line); }
        .btn-danger { background: var(--danger); }
        .btn-sm { padding: 7px 14px; font-size: 13px; }
        .hint { font-size: 13px; color: var(--ink-soft); margin-top: 6px; }
        .error-msg { color: var(--danger); font-size: 13px; margin-top: 6px; }
        .ok { background: #eef7e9; border: 1px solid var(--green-light); color: var(--green); border-radius: 12px; padding: 10px 14px; margin-bottom: 16px; font-size: 14px; }
        .warn { background: var(--warn-bg); border: 1px solid var(--warn-line); color: #7a5700; border-radius: 12px; padding: 10px 14px; margin-bottom: 14px; font-size: 14px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 640px) { .grid-2 { grid-template-columns: 1fr; } }
        .divider { border: none; border-top: 1px solid var(--line); margin: 18px 0; }
        .empty { color: var(--ink-soft); font-size: 14px; padding: 8px 0; }
        .checkbox-row { display: flex; gap: 8px; align-items: flex-start; font-size: 13px; color: var(--ink-soft); margin-top: 10px; }
        .checkbox-row input { width: auto; }
    </style>
</head>
<body class="app-shell">
    @include('partials.main-navbar')

    <div class="page">
        <div class="shell">
            <section class="card" aria-label="{{ __('Meine Vereine') }}" style="margin-bottom:0;">
                <div class="app-eyebrow mb-2">{{ __('Vereinsportal') }}</div>
                <h1 class="app-title mb-0">{{ __('Meine Vereine') }}</h1>
            </section>

            @if (session('status'))
                <div class="ok">{{ session('status') }}</div>
            @endif

            {{-- Aktive Mitgliedschaften --}}
            <div class="card">
                <h2>{{ __('Deine Vereinsmitgliedschaften') }}</h2>
                @forelse ($memberships as $membership)
                    <div class="membership-row">
                        <div>
                            <strong>{{ $membership->club->name }}</strong>
                            <div style="margin-top:4px;">
                                @foreach ($membership->roles as $role)
                                    <span class="role-badge {{ $role->role }}">{{ $roleLabels[$role->role] ?? $role->role }}</span>
                                @endforeach
                            </div>
                            <div class="hint">{{ __('Mitglied seit :date', ['date' => $membership->joined_at?->format('d.m.Y') ?? '–']) }}</div>
                        </div>
                        <a href="{{ route('clubs.show', $membership->club->slug) }}" class="btn btn-secondary btn-sm">{{ __('Vereinsbereich') }}</a>
                    </div>
                @empty
                    <p class="empty">{{ __('Du bist noch keinem Verein zugeordnet.') }}</p>
                @endforelse
            </div>

            <div class="grid-2">
            {{-- Bestehenden Verein beitreten --}}
            <div class="card">
                <h2>{{ __('Einem Verein beitreten') }}</h2>
                <p class="hint" style="margin-bottom:12px;">{{ __('Schicke eine Beitrittsanfrage an den Club-Manager eines bestehenden Vereins.') }}</p>

                @error('club_id')
                    <div class="error-msg" style="margin-bottom:10px;">{{ $message }}</div>
                @enderror

                <form method="post" action="{{ route('clubs.membership.join') }}">
                    @csrf
                    <label for="join_club_id">{{ __('Verein suchen & auswählen') }}</label>
                    <select id="join_club_id" name="club_id" required>
                        <option value="">{{ __('– Verein auswählen –') }}</option>
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}"
                                @selected(old('club_id') == $club->id)>{{ $club->name }}</option>
                        @endforeach
                    </select>
                    <div class="hint">{{ __('Nach der Anfrage bekommst du eine Bestätigung vom Vereins-Manager.') }}</div>
                    <div style="margin-top:12px;">
                        <button type="submit" class="btn">{{ __('Anfrage senden') }}</button>
                    </div>
                </form>
            </div>

            {{-- Neuen Verein anlegen --}}
            <div class="card">
                <h2>{{ __('Neuen Verein anlegen') }}</h2>
                <p class="hint" style="margin-bottom:12px;">{{ __('Du wirst automatisch als Club-Manager eingetragen.') }}</p>

                @error('club_name')
                    <div class="error-msg" style="margin-bottom:10px;">{{ $message }}</div>
                @enderror

                @if (session('club_duplicate_warning'))
                    @php $warn = session('club_duplicate_warning'); @endphp
                    <div class="warn">
                        Aehnlicher Verein gefunden: <strong>{{ $warn['name'] }}</strong>. Falls dein Verein wirklich neu ist, bestätige das unten.
                    </div>
                @endif

                <form method="post" action="{{ route('clubs.membership.create-club') }}">
                    @csrf
                    <label for="club_name">{{ __('Vereinsname') }}</label>
                    <input id="club_name" name="club_name" value="{{ old('club_name') }}" required autocomplete="off">

                    @if (session('club_duplicate_warning'))
                        <label class="checkbox-row" for="confirm_new">
                            <input type="checkbox" id="confirm_new" name="confirm_new" value="1" @checked(old('confirm_new'))>
                            <span>{{ __('Mein Verein ist wirklich neu und nicht im System vorhanden.') }}</span>
                        </label>
                    @endif

                    <div style="margin-top:12px;">
                        <button type="submit" class="btn">{{ __('Verein anlegen') }}</button>
                    </div>
                </form>
            </div>
            </div>

            {{-- Offene Anfragen --}}
            <div class="card">
                <h2>{{ __('Offene Beitrittsanfragen') }}</h2>
                @forelse ($openRequests as $req)
                    <div class="request-row">
                        <div>
                            <strong>{{ $req->club?->name ?? $req->requested_club_name }}</strong>
                            <div class="hint">{{ __('Gesendet :date', ['date' => $req->created_at->format('d.m.Y')]) }}</div>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <span class="badge-status pending">{{ __('Ausstehend') }}</span>
                            <form method="post" action="{{ route('clubs.membership.cancel', $req) }}" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">{{ __('Zurückziehen') }}</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="empty">{{ __('Keine offenen Anfragen.') }}</p>
                @endforelse
            </div>

            {{-- Zurückliegende Anfragen --}}
            @if ($recentRequests->isNotEmpty())
                <div class="card">
                    <h2>{{ __('Letzte Anfragen') }}</h2>
                    @foreach ($recentRequests as $req)
                        <div class="request-row">
                            <div>
                                <strong>{{ $req->club?->name ?? $req->requested_club_name }}</strong>
                                <div class="hint">{{ $req->created_at->format('d.m.Y') }}</div>
                            </div>
                            <span class="badge-status {{ $req->status }}">
                                @php
                                    $statusLabel = match($req->status) {
                                        'approved'  => __('Genehmigt'),
                                        'declined'  => __('Abgelehnt'),
                                        'cancelled' => __('Zurückgezogen'),
                                        default     => $req->status,
                                    };
                                @endphp
                                {{ $statusLabel }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @include('partials.main-footer')
    @include('partials.app-scripts')
</body>
</html>
