@extends('admin.layout')

@section('title', __('Admin Dashboard') . ' | BaseForFight')

@push('head')
    <style>
        .actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .hint {
            color: #4d6050;
            margin-top: 6px;
        }

        .kpi-grid {
            margin-bottom: 18px;
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
        }

        .kpi {
            border: 1px solid #c8d4c2;
            border-radius: 14px;
            background: #fff;
            padding: 12px;
        }

        .kpi-label {
            color: #4d6050;
            font-size: 12px;
        }

        .kpi-value {
            margin-top: 4px;
            font-size: 1.6rem;
            font-weight: 700;
            color: #016734;
        }

        .layout {
            display: grid;
            grid-template-columns: 0.7fr 0.3fr;
            gap: 14px;
        }

        .section-title {
            margin: 0 0 10px;
        }

        .endpoint-group {
            margin-top: 16px;
        }

        .endpoint-row {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 10px;
            margin-bottom: 8px;
            border: 1px solid #c8d4c2;
            border-radius: 10px;
            background: #fff;
            padding: 8px;
        }

        .method {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            background: #016734;
            min-height: 26px;
        }

        .endpoint-path {
            margin: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 13px;
            color: #2d3a2e;
        }

        .endpoint-text {
            color: #4d6050;
            font-size: 13px;
            margin-top: 3px;
        }

        .list {
            display: grid;
            gap: 8px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            border: 1px solid #c8d4c2;
            border-radius: 10px;
            background: #fff;
            padding: 9px 10px;
        }

        .pill {
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            color: #016734;
        }

        .quick-links {
            margin-top: 16px;
            display: grid;
            gap: 8px;
        }

        .quick-links a {
            text-decoration: none;
            color: #016734;
            font-weight: 700;
        }

        #user-switch-modal {
            border: 1px solid #c8d4c2;
            border-radius: 18px;
            padding: 0;
            width: min(900px, 96vw);
            background: #fafcf8;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.22);
        }

        .modal-head {
            padding: 16px 18px;
            border-bottom: 1px solid #c8d4c2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 16px 18px;
            max-height: 70vh;
            overflow: auto;
        }

        .user-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .user-card {
            border: 1px solid #c8d4c2;
            border-radius: 12px;
            padding: 12px;
            background: #fff;
        }

        .user-meta {
            margin-top: 8px;
            color: #4d6050;
            font-size: 13px;
            display: grid;
            gap: 3px;
        }

        @media (max-width: 1180px) {
            .kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .layout { grid-template-columns: 1fr; }
        }

        @media (max-width: 720px) {
            .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .user-grid { grid-template-columns: 1fr; }
            .endpoint-row { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    <div class="header">
        <div>
            <h1>{{ __('Operations Dashboard') }}</h1>
            <div class="hint"><img class="inline-illustration" src="{{ asset('assets/brand/icons/icon_organize.png') }}" alt="{{ __('Boxer organisiert Arbeitsablauf') }}">{{ __('Kernaufgaben zuerst: steuern, prüfen, bei Bedarf in Nutzerperspektive wechseln.') }}</div>
        </div>
        <div class="actions">
            @if (session()->has('impersonator_id'))
                <form method="post" action="{{ route('admin.impersonation.stop') }}">
                    @csrf
                    <button class="logout" type="submit" style="background:#7db928;">{{ __('Zurück') }}</button>
                </form>
            @endif
            <button class="logout" type="button" onclick="document.getElementById('user-switch-modal').showModal()" style="background:#016734;">{{ __('Ansicht wechseln') }}</button>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">{{ __('Logout') }}</button>
            </form>
        </div>
    </div>

    <section class="kpi-grid">
        <article class="kpi"><div class="kpi-label">{{ __('Benutzer') }}</div><div class="kpi-value">{{ $stats['users'] }}</div></article>
        <article class="kpi"><div class="kpi-label">{{ __('Clubs') }}</div><div class="kpi-value">{{ $stats['clubs'] }}</div></article>
        <article class="kpi"><div class="kpi-label">{{ __('Kämpfer') }}</div><div class="kpi-value">{{ $stats['fighters'] }}</div></article>
        <article class="kpi"><div class="kpi-label">{{ __('Events') }}</div><div class="kpi-value">{{ $stats['events'] }}</div></article>
        <article class="kpi"><div class="kpi-label">{{ __('Registrierungen') }}</div><div class="kpi-value">{{ $stats['registrations'] }}</div></article>
        <article class="kpi"><div class="kpi-label">{{ __('Offene Einladungen') }}</div><div class="kpi-value">{{ $stats['open_invitations'] }}</div></article>
    </section>

    <section class="layout">
        <div class="card">
            <h2 class="section-title">{{ __('Endpoint Navigator') }}</h2>
            @foreach ($endpointGroups as $group => $endpoints)
                <div class="endpoint-group">
                    <h3 style="margin:0 0 8px;">{{ $group }}</h3>
                    @foreach ($endpoints as $endpoint)
                        <div class="endpoint-row">
                            <span class="method">{{ $endpoint['method'] }}</span>
                            <div>
                                <p class="endpoint-path">{{ $endpoint['path'] }}</p>
                                <div class="endpoint-text">{{ $endpoint['description'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <aside class="card">
            <h2 class="section-title">{{ $isSuperAdmin ? 'Alle Vereine' : 'Deine Admin-Clubs' }}</h2>
            @if ($dashboardClubs->isEmpty())
                <p class="hint">Keine Vereinsdaten gefunden.</p>
            @else
                <div class="list">
                    @foreach ($dashboardClubs as $club)
                        <div class="row">
                            <span>{{ $club->name }}</span>
                            <span class="pill">{{ $club->fighters_count }} Kämpfer</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (! $isSuperAdmin && $adminClubs->isNotEmpty())
                <div class="hint" style="margin-top:10px;">Rollenansicht</div>
                <div class="list">
                    @foreach ($adminClubs as $club)
                        <div class="row">
                            <span>{{ $club->name }}</span>
                            <span class="pill">{{ $club->role }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <h2 class="section-title" style="margin-top:16px;">Quick Links</h2>
            <div class="quick-links">
                <a href="{{ url('/api/v1/health') }}" target="_blank" rel="noopener">Health Endpoint</a>
                <a href="{{ url('/docs/api/openapi.yaml') }}" target="_blank" rel="noopener">OpenAPI YAML</a>
                <a href="{{ url('/docs/baseforfight-laravel-konzept.md') }}" target="_blank" rel="noopener">Projektkonzept</a>
            </div>

            <p class="hint" style="margin-top:12px;">
                {{ $isSuperAdmin ? 'Ansicht wechseln zeigt alle User im System.' : 'Ansicht wechseln zeigt die ersten 20 User alphabetisch.' }}
            </p>
        </aside>
    </section>

    <dialog id="user-switch-modal">
        <form class="modal-head" method="dialog">
            <strong>Ansicht wechseln</strong>
            <button class="logout" type="submit" style="background:#dd6850;">Schließen</button>
        </form>
        <div class="modal-body">
            <div class="user-grid">
                @foreach ($switchableUsers as $user)
                    <article class="user-card">
                        <strong>{{ $user->name }}</strong><br>
                        <span class="hint" style="font-size:13px;">{{ $user->email }}</span>
                        <div class="user-meta">
                            @forelse ($user->memberships as $membership)
                                <div>
                                    {{ $membership->club?->name ?? 'Kein Verein' }} ·
                                    {{ $membership->roles->pluck('role')->map(fn ($role) => strtoupper($role))->implode(', ') ?: 'MITGLIED' }}
                                </div>
                            @empty
                                <div>Kein Verein</div>
                            @endforelse
                        </div>
                        <form method="post" action="{{ route('admin.impersonate.switch', $user) }}" style="margin-top:10px;">
                            @csrf
                            <button class="logout" type="submit" style="background:#016734; width:100%;">Als User testen</button>
                        </form>
                    </article>
                @endforeach
            </div>
        </div>
    </dialog>
@endsection