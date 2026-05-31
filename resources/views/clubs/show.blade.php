<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $club->name }} | BaseForFight</title>
    @include('partials.app-assets')
    <style>
        :root {
            --bg: #f4f6f2;
            --bg-alt: #e8ede4;
            --panel: #fafcf8;
            --ink: #2d3a2e;
            --ink-soft: #4d6050;
            --line: #c8d4c2;
            --accent: #016734;
            --accent-soft: #7db928;
            --danger: #dd6850;
            --shadow: 0 18px 40px rgba(45, 58, 46, 0.12);
        }

        * { box-sizing: border-box; }

        .page {
            width: min(1480px, calc(100% - 24px));
            margin: 0 auto;
            padding: 1rem 0 2rem;
        }

        .shell {
            display: grid;
            gap: 16px;
        }

        .panel {
            background: rgba(250, 252, 248, 0.9);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .header {
            padding: 16px 18px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        h1 {
            margin: 8px 0 0;
            font-size: clamp(1.7rem, 3.5vw, 2.5rem);
            line-height: 1.1;
        }

        .subtitle {
            margin-top: 6px;
            color: var(--ink-soft);
            max-width: 64ch;
        }

        .status {
            padding: 10px 12px;
            border: 1px solid var(--accent-soft);
            background: #eef7e9;
            border-radius: 12px;
        }

        .error {
            padding: 10px 12px;
            border: 1px solid #e2a29a;
            background: #fff0ed;
            color: #7d2c1f;
            border-radius: 12px;
        }

        .error ul {
            margin: 0;
            padding-left: 18px;
        }

        .nav-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            border-bottom: 1px solid var(--line);
            padding: 0 18px;
            margin-top: 2px;
        }

        .nav-link {
            text-decoration: none;
            color: var(--ink-soft);
            font-weight: 700;
            padding: 10px 14px;
            border: 1px solid transparent;
            border-bottom: 0;
            border-radius: 10px 10px 0 0;
            margin-bottom: -1px;
            transition: 150ms ease;
        }

        .nav-link:hover {
            color: var(--ink);
            background: #f2f6ef;
        }

        .nav-link.active {
            color: var(--accent);
            background: #fff;
            border-color: var(--line);
        }

        .content {
            padding: 18px;
        }

        .section-title {
            margin: 0 0 12px;
            font-size: 1.2rem;
        }

        .muted { color: var(--ink-soft); }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .stat {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--panel);
            padding: 12px;
        }

        .stat-label {
            color: var(--ink-soft);
            font-size: 13px;
        }

        .stat-value {
            margin-top: 4px;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent);
        }

        .list {
            display: grid;
            gap: 10px;
        }

        .row {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--panel);
            padding: 12px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: #e9f3e4;
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .form-row {
            display: grid;
            gap: 6px;
        }

        label {
            font-weight: 700;
            font-size: 0.95rem;
        }

        input,
        textarea,
        select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 12px;
            font: inherit;
            background: #fff;
        }

        textarea { min-height: 100px; resize: vertical; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 999px;
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            padding: 10px 16px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-soft {
            background: #eff5ec;
            color: var(--ink);
            border: 1px solid var(--line);
        }

        .event-card {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            padding: 12px;
        }

        .event-card + .event-card {
            margin-top: 10px;
        }

        .event-meta {
            margin-top: 6px;
            font-size: 0.92rem;
            color: var(--ink-soft);
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .event-modal-form {
            padding: 14px;
            max-height: 78vh;
            overflow: auto;
            display: grid;
            gap: 16px;
        }

        .event-modal-topline {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .event-organizer-note {
            font-size: 0.92rem;
            color: var(--ink-soft);
        }

        .option-pill-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .option-pill {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0;
            border-radius: 999px;
            cursor: pointer;
        }

        .option-pill input {
            position: absolute;
            inset: 0;
            opacity: 0;
            pointer-events: none;
        }

        .option-pill span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid #d8a39a;
            background: #fff1ee;
            color: #9a3d2b;
            font-weight: 700;
            transition: 160ms ease;
        }

        .option-pill[data-kind="sport"] span {
            border-color: var(--line);
            background: #fff;
            color: var(--ink-soft);
        }

        .option-pill.is-active span {
            border-color: #7db928;
            background: #eff7ea;
            color: #016734;
            box-shadow: 0 8px 18px rgba(1, 103, 52, 0.12);
        }

        .event-status-card {
            min-width: 220px;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: linear-gradient(160deg, #f5faf0, #ffffff);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }

        .event-modal-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--line);
        }

        .event-modal-tab-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            border: 1px solid var(--line);
            border-bottom: 0;
            border-radius: 10px 10px 0 0;
            background: #f0f4ec;
            color: var(--ink-soft);
            padding: 9px 14px 10px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: -1px;
        }

        .event-modal-tab-link.is-active {
            background: #fff;
            color: var(--accent);
            border-color: var(--line);
        }

        .fighter-module-tab-btn,
        .fighter-boxing-tab-btn {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            border: 1px solid var(--line);
            border-bottom: 0;
            border-radius: 10px 10px 0 0;
            background: #f0f4ec;
            color: var(--ink-soft);
            padding: 9px 14px 10px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: -1px;
        }

        .fighter-module-tab-btn.active,
        .fighter-boxing-tab-btn.active {
            background: #fff;
            color: var(--accent);
            border-color: var(--line);
        }

        .fighter-module-tab-btn[hidden],
        .fighter-boxing-tab-btn[hidden] {
            display: none !important;
        }

        .event-tab-panel {
            display: none;
            gap: 14px;
        }

        .event-tab-panel.is-active {
            display: grid;
        }

        .event-section-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.9);
            padding: 14px;
            display: grid;
            gap: 12px;
        }

        .event-section-title {
            margin: 0;
            font-size: 1rem;
        }

        .event-section-note {
            margin-top: -6px;
            color: var(--ink-soft);
            font-size: 0.9rem;
        }

        .address-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.3fr) minmax(110px, 0.5fr) minmax(0, 1fr);
            gap: 12px;
        }

        .currency-field {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 92px;
            gap: 8px;
        }

        .event-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .event-summary-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
            padding: 14px;
            display: grid;
            gap: 10px;
        }

        .event-summary-label {
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--ink-soft);
        }

        .event-summary-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
        }

        .event-summary-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .event-summary-empty {
            color: var(--ink-soft);
            font-size: 0.92rem;
        }

        .event-age-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 8px;
        }

        .event-age-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 6px 10px;
            background: #fff;
            color: var(--ink);
            font-weight: 700;
            cursor: pointer;
            font: inherit;
            text-decoration: none;
        }

        .event-age-chip.is-active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .event-weight-list {
            margin: 0;
            padding-left: 18px;
            color: var(--ink-soft);
        }

        .registration-loading {
            padding: 14px;
            border: 1px dashed var(--line);
            border-radius: 12px;
            background: #f7faf5;
            color: var(--ink-soft);
        }

        .registration-toolbar {
            display: grid;
            grid-template-columns: minmax(140px, 0.8fr) minmax(220px, 1.3fr) minmax(170px, 0.9fr) minmax(170px, 0.9fr) auto auto;
            gap: 10px;
            align-items: end;
        }

        .registration-toolbar-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .management-topline {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }

        .management-kpis {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .kpi-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            padding: 12px;
            display: grid;
            gap: 4px;
        }

        .registration-results-meta {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            flex-wrap: wrap;
            color: var(--ink-soft);
            font-size: 0.92rem;
        }

        .registration-group-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.95);
            padding: 14px;
            display: grid;
            gap: 12px;
        }

        .registration-group-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .registration-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 860px;
        }

        .registration-table th,
        .registration-table td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        .registration-table th {
            color: var(--ink-soft);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .table-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid var(--line);
            background: #f2f6ef;
            color: var(--ink);
        }

        .status-badge.active {
            background: #e9f6ec;
            color: #0d6b32;
            border-color: #b8dcc0;
        }

        .status-badge.waiting {
            background: #fff6e8;
            color: #8c5a00;
            border-color: #e7cf97;
        }

        .status-badge.withdrawn {
            background: #fff0ed;
            color: #8d3c2f;
            border-color: #e2b0a6;
        }

        .billable-flag {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            background: #eef7e9;
            color: var(--accent);
            border: 1px solid #cfe3bf;
        }

        @media (max-width: 920px) {
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .form-grid { grid-template-columns: 1fr; }
            .address-grid { grid-template-columns: 1fr 1fr; }
            .event-summary-grid { grid-template-columns: 1fr; }
            .registration-toolbar { grid-template-columns: 1fr 1fr; }
            .management-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 640px) {
            .row { flex-direction: column; }
            .stat-grid { grid-template-columns: 1fr; }
            .address-grid, .currency-field { grid-template-columns: 1fr; }
            .registration-toolbar { grid-template-columns: 1fr; }
            .management-kpis { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="app-shell">
    @include('partials.main-navbar')

    <div class="page">
        <div class="shell">
            @if (session()->has('impersonator_id'))
                <section class="panel" style="padding:10px 14px; display:flex; justify-content:space-between; align-items:center; gap:10px;">
                    <strong style="font-size:0.95rem;">Support-Simulation aktiv</strong>
                    <form method="post" action="{{ route('admin.impersonation.stop') }}">
                        @csrf
                        <button class="btn" type="submit">Zurück zum Superadmin-Dashboard</button>
                    </form>
                </section>
            @endif

            <section class="panel">
                <div class="header">
                    <div>
                        <div class="app-eyebrow mb-2">{{ __('Vereinsportal') }}</div>
                        <h1>{{ $club->name }}</h1>
                        <div class="subtitle">Vereinsansicht. Hier regelst du alles für deinen Verein. Zusammen mit deinen Trainern siehst du die Daten zu deinen Athleten und Veranstaltungen.</div>
                    </div>
                </div>
                

                <nav class="nav-tabs" aria-label="Club Reiter">
                    <a class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" href="{{ route('clubs.show', ['club' => $club->slug, 'tab' => 'overview']) }}">Übersicht</a>
                    <a class="nav-link {{ $activeTab === 'fighters' ? 'active' : '' }}" href="{{ route('clubs.show', ['club' => $club->slug, 'tab' => 'fighters']) }}">Kämpfer</a>
                    <a class="nav-link {{ $activeTab === 'trainers' ? 'active' : '' }}" href="{{ route('clubs.show', ['club' => $club->slug, 'tab' => 'trainers']) }}">Trainer</a>
                    <a class="nav-link {{ $activeTab === 'events' ? 'active' : '' }}" href="{{ route('clubs.show', ['club' => $club->slug, 'tab' => 'events']) }}">Veranstaltungen</a>
                    @if ($canManageClub)
                        <a class="nav-link {{ $activeTab === 'club-data' ? 'active' : '' }}" href="{{ route('clubs.show', ['club' => $club->slug, 'tab' => 'club-data']) }}">Vereinsdaten</a>
                        <a class="nav-link {{ $activeTab === 'billing' ? 'active' : '' }}" href="{{ route('clubs.show', ['club' => $club->slug, 'tab' => 'billing']) }}">Rechnungsdaten</a>
                    @endif
                </nav>

                <div class="content">
                    @if (session('status'))
                        <div class="status" style="margin-bottom:12px;">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="error" style="margin-bottom:12px;">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                @if ($activeTab === 'overview')
                    <h2 class="section-title">Vereinsübersicht</h2>
                    <div class="stat-grid">
                        <article class="stat">
                            <div class="stat-label">Trainer</div>
                            <div class="stat-value">{{ $trainers->count() }}</div>
                        </article>
                        <article class="stat">
                            <div class="stat-label">Kämpfer</div>
                            <div class="stat-value">{{ $fighters->count() }}</div>
                        </article>
                        <article class="stat">
                            <div class="stat-label">Veranstaltungen</div>
                            <div class="stat-value">{{ $clubEvents->count() }}</div>
                        </article>
                        <article class="stat">
                            <div class="stat-label">Deine Rolle</div>
                            <div class="stat-value" style="font-size:1rem;">{{ $roleSummary }}</div>
                        </article>
                    </div>
                @elseif ($activeTab === 'fighters')
                    <h2 class="section-title">Kämpferliste</h2>

                    @php
                        $activeSportModules = (array) ($activeSportModules ?? []);
                        $activeSportModuleSlugs = array_values(array_filter(array_map(fn (array $module): string => (string) ($module['slug'] ?? ''), $activeSportModules), fn (string $slug): bool => $slug !== ''));
                        $openFighterId = (int) request()->query('edit_fighter', 0);
                        $returnEventId = (int) request()->query('return_event', 0);
                        $defaultCreateFighterModules = (array) old('sport_modules', in_array('boxing', $activeSportModuleSlugs, true) ? ['boxing'] : []);
                        $boxingPassKeywords = array_values(array_filter((array) ($boxingPassKeywords ?? ['Arzt gültig bis', 'KO-Sperre gültig bis', 'Registrierung gültig bis']), fn ($keyword) => is_string($keyword) && $keyword !== ''));
                        $createWeightDates = (array) old('boxing_weight_dates', ['']);
                        $createWeightValues = (array) old('boxing_weight_values', ['']);
                        $createBoutDates = (array) old('boxing_bout_dates', ['']);
                        $createBoutWins = (array) old('boxing_bout_wins', ['']);
                        $createBoutLosses = (array) old('boxing_bout_losses', ['']);
                        $createBoutDraws = (array) old('boxing_bout_draws', ['']);
                        $createPassKeywords = (array) old('boxing_pass_keywords', $boxingPassKeywords);
                        $createPassDates = (array) old('boxing_pass_dates', array_fill(0, count($createPassKeywords), ''));
                        if (count($createPassKeywords) === 0) {
                            $createPassKeywords = [''];
                        }
                    @endphp

                    @if ($canManageAthletes)
                        <div style="margin-bottom:12px; display:flex; justify-content:flex-end;">
                            <button type="button" class="btn" data-open-modal="create-fighter-modal">Neuen Kämpfer anlegen</button>
                        </div>

                        <dialog id="create-fighter-modal" style="width:min(980px, calc(100% - 24px)); border:1px solid var(--line); border-radius:14px; padding:0;">
                            <form method="dialog" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid var(--line); background:#f7faf5;">
                                <strong>Neuer Kämpfer</strong>
                                <button class="btn btn-soft" type="submit">Schließen</button>
                            </form>
                            <form method="post" action="{{ route('clubs.fighters.store', $club) }}" style="padding:14px; max-height:75vh; overflow:auto;" data-fighter-form="create-fighter">
                                @csrf

                                <div class="form-grid" style="margin-top:4px;">
                                    <div class="form-row">
                                        <label for="fighter_first_name_create">Vorname</label>
                                        <input id="fighter_first_name_create" name="first_name" value="{{ old('first_name') }}" required>
                                    </div>
                                    <div class="form-row">
                                        <label for="fighter_last_name_create">Nachname</label>
                                        <input id="fighter_last_name_create" name="last_name" value="{{ old('last_name') }}" required>
                                    </div>
                                    <div class="form-row">
                                        <label for="fighter_birth_date_create">Geburtsdatum</label>
                                        <input id="fighter_birth_date_create" type="date" name="birth_date" value="{{ old('birth_date') }}">
                                    </div>
                                    <div class="form-row">
                                        <label for="fighter_sex_create">Geschlecht</label>
                                        <select id="fighter_sex_create" name="sex" required>
                                            <option value="m" @selected(old('sex', 'm') === 'm')>männlich</option>
                                            <option value="w" @selected(old('sex') === 'w')>weiblich</option>
                                        </select>
                                    </div>
                                    <div class="form-row">
                                        <label for="fighter_status_create">Status</label>
                                        <select id="fighter_status_create" name="status">
                                            <option value="active" @selected(old('status', 'active') === 'active')>active</option>
                                            <option value="inactive" @selected(old('status') === 'inactive')>inactive</option>
                                            <option value="suspended" @selected(old('status') === 'suspended')>suspended</option>
                                        </select>
                                    </div>
                                    <div class="form-row" style="grid-column:1 / -1;">
                                        <label>Sportmodule</label>
                                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                            @forelse ($activeSportModules as $module)
                                                @php
                                                    $moduleSlug = (string) ($module['slug'] ?? '');
                                                    $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                                @endphp
                                                @if ($moduleSlug !== '')
                                                    <label style="display:flex; align-items:center; gap:6px; border:1px solid var(--line); border-radius:999px; padding:7px 10px; background:#fff;">
                                                        <input
                                                            type="checkbox"
                                                            name="sport_modules[]"
                                                            value="{{ $moduleSlug }}"
                                                            class="fighter-module-checkbox"
                                                            data-fighter-target="create-fighter"
                                                            @checked(in_array($moduleSlug, $defaultCreateFighterModules, true))
                                                        >
                                                        <span>{{ $moduleName }}</span>
                                                    </label>
                                                @endif
                                            @empty
                                                <div class="muted">Keine Sportmodule aktiv!</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $activeCreateModuleTabSlug = collect($activeSportModules)
                                        ->map(fn ($module) => (string) ($module['slug'] ?? ''))
                                        ->first(fn ($slug) => $slug !== '' && in_array($slug, $defaultCreateFighterModules, true)) ?? '';
                                @endphp
                                <div data-fighter-module-tabs="create-fighter" role="tablist" aria-label="Sportmodule" style="display:flex; gap:8px; flex-wrap:wrap; margin-top:16px; border-top:1px solid var(--line); padding-top:14px;">
                                    @foreach ($activeSportModules as $module)
                                        @php
                                            $moduleSlug = (string) ($module['slug'] ?? '');
                                            $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                            $isSelected = in_array($moduleSlug, $defaultCreateFighterModules, true);
                                            $isTabActive = $isSelected && $moduleSlug === $activeCreateModuleTabSlug;
                                        @endphp
                                        @if ($moduleSlug !== '')
                                            <a href="#fighter-module-panel-create-fighter-{{ $moduleSlug }}" id="fighter-module-tab-create-fighter-{{ $moduleSlug }}" role="tab" aria-selected="{{ $isTabActive ? 'true' : 'false' }}" aria-controls="fighter-module-panel-create-fighter-{{ $moduleSlug }}" tabindex="{{ $isTabActive ? '0' : '-1' }}" class="fighter-module-tab-btn{{ $isTabActive ? ' active' : '' }}" data-fighter-tab="create-fighter-{{ $moduleSlug }}" @if (! $isSelected) hidden @endif>{{ $moduleName }}</a>
                                        @endif
                                    @endforeach
                                </div>

                                @foreach ($activeSportModules as $module)
                                    @php
                                        $moduleSlug = (string) ($module['slug'] ?? '');
                                        $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                        $isSelected = in_array($moduleSlug, $defaultCreateFighterModules, true);
                                    @endphp
                                    @if ($moduleSlug !== '')
                                        <section id="fighter-module-panel-create-fighter-{{ $moduleSlug }}" role="tabpanel" aria-labelledby="fighter-module-tab-create-fighter-{{ $moduleSlug }}" data-fighter-module-panel="create-fighter-{{ $moduleSlug }}" @if (! ($isSelected && $moduleSlug === $activeCreateModuleTabSlug)) hidden @endif style="display:{{ $isSelected && $moduleSlug === $activeCreateModuleTabSlug ? 'block' : 'none' }}; margin-top:12px; border:1px solid var(--line); border-radius:12px; padding:12px; background:#fff;">
                                            @if ($moduleSlug === 'boxing')
                                                <div style="display:flex; justify-content:space-between; gap:8px; align-items:center; margin-bottom:10px;">
                                                    <strong>{{ $moduleName }} - Details</strong>
                                                </div>

                                                <div role="tablist" aria-label="Box-Details" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                                                    <a href="#fighter-boxing-panel-create-boxing-weight" id="fighter-boxing-tab-create-boxing-weight" role="tab" aria-selected="true" aria-controls="fighter-boxing-panel-create-boxing-weight" tabindex="0" class="fighter-boxing-tab-btn active" data-boxing-tab="create-boxing-weight">Aktuelles Gewicht</a>
                                                    <a href="#fighter-boxing-panel-create-boxing-bouts" id="fighter-boxing-tab-create-boxing-bouts" role="tab" aria-selected="false" aria-controls="fighter-boxing-panel-create-boxing-bouts" tabindex="-1" class="fighter-boxing-tab-btn" data-boxing-tab="create-boxing-bouts">Aktuelle Kampfzahl</a>
                                                    <a href="#fighter-boxing-panel-create-boxing-pass" id="fighter-boxing-tab-create-boxing-pass" role="tab" aria-selected="false" aria-controls="fighter-boxing-panel-create-boxing-pass" tabindex="-1" class="fighter-boxing-tab-btn" data-boxing-tab="create-boxing-pass">Kampfpass</a>
                                                </div>

                                                <div id="fighter-boxing-panel-create-boxing-weight" role="tabpanel" aria-labelledby="fighter-boxing-tab-create-boxing-weight" data-boxing-tab-panel="create-boxing-weight" style="display:block;">
                                                    <div data-boxing-row-container="weight" style="display:grid; gap:8px;">
                                                        @php $createWeightRows = max(count($createWeightDates), count($createWeightValues), 1); @endphp
                                                        @for ($i = 0; $i < $createWeightRows; $i++)
                                                            <div data-boxing-row style="display:grid; grid-template-columns:180px 180px auto; gap:8px;">
                                                                <input type="date" name="boxing_weight_dates[]" value="{{ (string) ($createWeightDates[$i] ?? '') }}">
                                                                <input type="number" step="0.01" min="0" name="boxing_weight_values[]" value="{{ (string) ($createWeightValues[$i] ?? '') }}" placeholder="kg">
                                                                <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                    <div style="margin-top:8px;">
                                                        <button type="button" class="btn btn-soft" data-add-boxing-row="weight">Zeile hinzufuegen</button>
                                                    </div>
                                                </div>

                                                <div id="fighter-boxing-panel-create-boxing-bouts" role="tabpanel" aria-labelledby="fighter-boxing-tab-create-boxing-bouts" data-boxing-tab-panel="create-boxing-bouts" hidden style="display:none;">
                                                    <div data-boxing-row-container="bouts" style="display:grid; gap:8px;">
                                                        @php $createBoutRows = max(count($createBoutDates), count($createBoutWins), count($createBoutLosses), count($createBoutDraws), 1); @endphp
                                                        @for ($i = 0; $i < $createBoutRows; $i++)
                                                            <div data-boxing-row style="display:grid; grid-template-columns:180px repeat(3, 110px) auto; gap:8px;">
                                                                <input type="date" name="boxing_bout_dates[]" value="{{ (string) ($createBoutDates[$i] ?? '') }}">
                                                                <input type="number" min="0" name="boxing_bout_wins[]" value="{{ (string) ($createBoutWins[$i] ?? '') }}" placeholder="Siege">
                                                                <input type="number" min="0" name="boxing_bout_losses[]" value="{{ (string) ($createBoutLosses[$i] ?? '') }}" placeholder="Niederlagen">
                                                                <input type="number" min="0" name="boxing_bout_draws[]" value="{{ (string) ($createBoutDraws[$i] ?? '') }}" placeholder="Unentschieden">
                                                                <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                    <div style="margin-top:8px;">
                                                        <button type="button" class="btn btn-soft" data-add-boxing-row="bouts">Zeile hinzufuegen</button>
                                                    </div>
                                                </div>

                                                <div id="fighter-boxing-panel-create-boxing-pass" role="tabpanel" aria-labelledby="fighter-boxing-tab-create-boxing-pass" data-boxing-tab-panel="create-boxing-pass" hidden style="display:none;">
                                                    <div data-boxing-row-container="pass" style="display:grid; gap:8px;">
                                                        @php $createPassRows = max(count($createPassKeywords), count($createPassDates), 1); @endphp
                                                        @for ($i = 0; $i < $createPassRows; $i++)
                                                            <div data-boxing-row style="display:grid; grid-template-columns:1.4fr 180px auto; gap:8px;">
                                                                <input name="boxing_pass_keywords[]" value="{{ (string) ($createPassKeywords[$i] ?? '') }}" placeholder="Stichwort">
                                                                <input type="date" name="boxing_pass_dates[]" value="{{ (string) ($createPassDates[$i] ?? '') }}">
                                                                <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                            </div>
                                                        @endfor
                                                    </div>
                                                    <div style="margin-top:8px;">
                                                        <button type="button" class="btn btn-soft" data-add-boxing-row="pass">Zeile hinzufuegen</button>
                                                    </div>
                                                </div>

                                                <template data-boxing-row-template="weight">
                                                    <div data-boxing-row style="display:grid; grid-template-columns:180px 180px auto; gap:8px; margin-top:8px;">
                                                        <input type="date" name="boxing_weight_dates[]">
                                                        <input type="number" step="0.01" min="0" name="boxing_weight_values[]" placeholder="kg">
                                                        <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                    </div>
                                                </template>
                                                <template data-boxing-row-template="bouts">
                                                    <div data-boxing-row style="display:grid; grid-template-columns:180px repeat(3, 110px) auto; gap:8px; margin-top:8px;">
                                                        <input type="date" name="boxing_bout_dates[]">
                                                        <input type="number" min="0" name="boxing_bout_wins[]" placeholder="Siege">
                                                        <input type="number" min="0" name="boxing_bout_losses[]" placeholder="Niederlagen">
                                                        <input type="number" min="0" name="boxing_bout_draws[]" placeholder="Unentschieden">
                                                        <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                    </div>
                                                </template>
                                                <template data-boxing-row-template="pass">
                                                    <div data-boxing-row style="display:grid; grid-template-columns:1.4fr 180px auto; gap:8px; margin-top:8px;">
                                                        <input name="boxing_pass_keywords[]" placeholder="Stichwort">
                                                        <input type="date" name="boxing_pass_dates[]">
                                                        <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                    </div>
                                                </template>
                                            @else
                                                <strong>{{ $moduleName }}</strong>
                                                <div class="muted" style="margin-top:6px;">Sportartspezifische Details für dieses Modul folgen.</div>
                                            @endif
                                        </section>
                                    @endif
                                @endforeach

                                <div style="margin-top:14px; display:flex; justify-content:flex-end;">
                                    <button class="btn" type="submit">Speichern</button>
                                </div>
                            </form>
                        </dialog>
                    @endif

                    <div class="list">
                        @forelse ($fighters as $fighter)
                            <article class="row" style="align-items:center;">
                                <div style="min-width:0; flex:1;">
                                    <strong>{{ trim($fighter->first_name . ' ' . $fighter->last_name) }}</strong>
                                    <div class="muted" style="margin-top:4px;">Status: {{ $fighter->status ?? 'active' }}</div>
                                    @php
                                        $latestWeightEntry = collect((array) ($fighter->boxing_weight_entries ?? []))
                                            ->filter(fn ($entry) => is_array($entry) && trim((string) ($entry['date'] ?? '')) !== '')
                                            ->sortByDesc(fn ($entry) => (string) ($entry['date'] ?? ''))
                                            ->first();
                                        $latestBoutEntry = collect((array) ($fighter->boxing_bout_count_entries ?? []))
                                            ->filter(fn ($entry) => is_array($entry) && trim((string) ($entry['date'] ?? '')) !== '')
                                            ->sortByDesc(fn ($entry) => (string) ($entry['date'] ?? ''))
                                            ->first();
                                        $totalWins = collect((array) ($fighter->boxing_bout_count_entries ?? []))->sum(fn ($entry) => is_array($entry) && is_numeric($entry['wins'] ?? null) ? (int) $entry['wins'] : 0);
                                        $totalLosses = collect((array) ($fighter->boxing_bout_count_entries ?? []))->sum(fn ($entry) => is_array($entry) && is_numeric($entry['losses'] ?? null) ? (int) $entry['losses'] : 0);
                                        $totalDraws = collect((array) ($fighter->boxing_bout_count_entries ?? []))->sum(fn ($entry) => is_array($entry) && is_numeric($entry['draws'] ?? null) ? (int) $entry['draws'] : 0);
                                        $totalFights = $totalWins + $totalLosses + $totalDraws;
                                    @endphp
                                    @foreach ((array) ($fighter->sport_modules ?? []) as $fighterModule)
                                        @if ((string) $fighterModule === 'boxing')
                                            <div class="muted" style="margin-top:2px;">
                                                Boxen: aktuelles Gewicht
                                                {{ is_array($latestWeightEntry) && array_key_exists('weight_kg', $latestWeightEntry) && $latestWeightEntry['weight_kg'] !== null ? ((string) $latestWeightEntry['weight_kg'] . ' kg') : '-' }}
                                                ({{ (string) ($latestWeightEntry['date'] ?? '-') }}),
                                                Statistik Gesamt {{ $totalFights }} | S {{ $totalWins }} | N {{ $totalLosses }} | U {{ $totalDraws }}
                                            </div>
                                        @else
                                            <div class="muted" style="margin-top:2px;">{{ ucfirst((string) $fighterModule) }}: keine sportartspezifische Statistik konfiguriert.</div>
                                        @endif
                                    @endforeach
                                </div>
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                                    @foreach ((array) ($fighter->sport_modules ?? []) as $fighterModule)
                                        <span class="pill">{{ strtoupper((string) $fighterModule) }}</span>
                                    @endforeach
                                    @if ($canManageAthletes)
                                        <button type="button" class="btn btn-soft" data-open-modal="edit-fighter-modal-{{ $fighter->getKey() }}">Bearbeiten</button>
                                    @endif
                                </div>
                            </article>

                            @if ($canManageAthletes)
                                @php
                                    $fighterModules = (array) old('sport_modules', (array) ($fighter->sport_modules ?? []));
                                    $fighterWeightEntries = (array) ($fighter->boxing_weight_entries ?? []);
                                    $fighterBoutEntries = (array) ($fighter->boxing_bout_count_entries ?? []);
                                    $fighterPassEntries = (array) ($fighter->boxing_pass_entries ?? []);
                                @endphp
                                <dialog id="edit-fighter-modal-{{ $fighter->getKey() }}" style="width:min(980px, calc(100% - 24px)); border:1px solid var(--line); border-radius:14px; padding:0;">
                                    <form method="dialog" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid var(--line); background:#f7faf5;">
                                        <strong>Kämpfer bearbeiten</strong>
                                        <button class="btn btn-soft" type="submit">Schließen</button>
                                    </form>
                                    <form method="post" action="{{ route('clubs.fighters.update', ['club' => $club, 'fighter' => $fighter]) }}" style="padding:14px; max-height:75vh; overflow:auto;" data-fighter-form="edit-fighter-{{ $fighter->getKey() }}">
                                        @csrf
                                        @method('patch')
                                        @if ($returnEventId > 0)
                                            <input type="hidden" name="return_event_id" value="{{ $returnEventId }}">
                                        @endif

                                        <div class="form-grid" style="margin-top:4px;">
                                            <div class="form-row">
                                                <label>Vorname</label>
                                                <input name="first_name" value="{{ old('first_name', $fighter->first_name) }}" required>
                                            </div>
                                            <div class="form-row">
                                                <label>Nachname</label>
                                                <input name="last_name" value="{{ old('last_name', $fighter->last_name) }}" required>
                                            </div>
                                            <div class="form-row">
                                                <label>Geburtsdatum</label>
                                                <input type="date" name="birth_date" value="{{ old('birth_date', $fighter->birth_date?->format('Y-m-d')) }}">
                                            </div>
                                            <div class="form-row">
                                                <label>Geschlecht</label>
                                                <select name="sex" required>
                                                    <option value="m" @selected(old('sex', $fighter->sex ?? 'm') === 'm')>männlich</option>
                                                    <option value="w" @selected(old('sex', $fighter->sex) === 'w')>weiblich</option>
                                                </select>
                                            </div>
                                            <div class="form-row">
                                                <label>Status</label>
                                                <select name="status">
                                                    <option value="active" @selected(old('status', $fighter->status) === 'active')>active</option>
                                                    <option value="inactive" @selected(old('status', $fighter->status) === 'inactive')>inactive</option>
                                                    <option value="suspended" @selected(old('status', $fighter->status) === 'suspended')>suspended</option>
                                                </select>
                                            </div>
                                            <div class="form-row" style="grid-column:1 / -1;">
                                                <label>Sportmodule</label>
                                                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                                    @forelse ($activeSportModules as $module)
                                                        @php
                                                            $moduleSlug = (string) ($module['slug'] ?? '');
                                                            $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                                        @endphp
                                                        @if ($moduleSlug !== '')
                                                            <label style="display:flex; align-items:center; gap:6px; border:1px solid var(--line); border-radius:999px; padding:7px 10px; background:#fff;">
                                                                <input
                                                                    type="checkbox"
                                                                    name="sport_modules[]"
                                                                    value="{{ $moduleSlug }}"
                                                                    class="fighter-module-checkbox"
                                                                    data-fighter-target="edit-fighter-{{ $fighter->getKey() }}"
                                                                    @checked(in_array($moduleSlug, $fighterModules, true))
                                                                >
                                                                <span>{{ $moduleName }}</span>
                                                            </label>
                                                        @endif
                                                    @empty
                                                        <div class="muted">Keine Sportmodule aktiv!</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                        @php
                                            $activeEditModuleTabSlug = collect($activeSportModules)
                                                ->map(fn ($module) => (string) ($module['slug'] ?? ''))
                                                ->first(fn ($slug) => $slug !== '' && in_array($slug, $fighterModules, true)) ?? '';
                                        @endphp
                                        <div data-fighter-module-tabs="edit-fighter-{{ $fighter->getKey() }}" role="tablist" aria-label="Sportmodule" style="display:flex; gap:8px; flex-wrap:wrap; margin-top:16px; border-top:1px solid var(--line); padding-top:14px;">
                                            @foreach ($activeSportModules as $module)
                                                @php
                                                    $moduleSlug = (string) ($module['slug'] ?? '');
                                                    $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                                    $isSelected = in_array($moduleSlug, $fighterModules, true);
                                                    $isTabActive = $isSelected && $moduleSlug === $activeEditModuleTabSlug;
                                                @endphp
                                                @if ($moduleSlug !== '')
                                                    <a href="#fighter-module-panel-edit-fighter-{{ $fighter->getKey() }}-{{ $moduleSlug }}" id="fighter-module-tab-edit-fighter-{{ $fighter->getKey() }}-{{ $moduleSlug }}" role="tab" aria-selected="{{ $isTabActive ? 'true' : 'false' }}" aria-controls="fighter-module-panel-edit-fighter-{{ $fighter->getKey() }}-{{ $moduleSlug }}" tabindex="{{ $isTabActive ? '0' : '-1' }}" class="fighter-module-tab-btn{{ $isTabActive ? ' active' : '' }}" data-fighter-tab="edit-fighter-{{ $fighter->getKey() }}-{{ $moduleSlug }}" @if (! $isSelected) hidden @endif>{{ $moduleName }}</a>
                                                @endif
                                            @endforeach
                                        </div>

                                        @foreach ($activeSportModules as $module)
                                            @php
                                                $moduleSlug = (string) ($module['slug'] ?? '');
                                                $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                                $isSelected = in_array($moduleSlug, $fighterModules, true);
                                            @endphp
                                            @if ($moduleSlug !== '')
                                                <section id="fighter-module-panel-edit-fighter-{{ $fighter->getKey() }}-{{ $moduleSlug }}" role="tabpanel" aria-labelledby="fighter-module-tab-edit-fighter-{{ $fighter->getKey() }}-{{ $moduleSlug }}" data-fighter-module-panel="edit-fighter-{{ $fighter->getKey() }}-{{ $moduleSlug }}" @if (! ($isSelected && $moduleSlug === $activeEditModuleTabSlug)) hidden @endif style="display:{{ $isSelected && $moduleSlug === $activeEditModuleTabSlug ? 'block' : 'none' }}; margin-top:12px; border:1px solid var(--line); border-radius:12px; padding:12px; background:#fff;">
                                                    @if ($moduleSlug === 'boxing')
                                                        <div style="display:flex; justify-content:space-between; gap:8px; align-items:center; margin-bottom:10px;">
                                                            <strong>{{ $moduleName }} - Details</strong>
                                                        </div>

                                                        <div role="tablist" aria-label="Box-Details" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                                                            <a href="#fighter-boxing-panel-edit-{{ $fighter->getKey() }}-boxing-weight" id="fighter-boxing-tab-edit-{{ $fighter->getKey() }}-boxing-weight" role="tab" aria-selected="true" aria-controls="fighter-boxing-panel-edit-{{ $fighter->getKey() }}-boxing-weight" tabindex="0" class="fighter-boxing-tab-btn active" data-boxing-tab="edit-{{ $fighter->getKey() }}-boxing-weight">Aktuelles Gewicht</a>
                                                            <a href="#fighter-boxing-panel-edit-{{ $fighter->getKey() }}-boxing-bouts" id="fighter-boxing-tab-edit-{{ $fighter->getKey() }}-boxing-bouts" role="tab" aria-selected="false" aria-controls="fighter-boxing-panel-edit-{{ $fighter->getKey() }}-boxing-bouts" tabindex="-1" class="fighter-boxing-tab-btn" data-boxing-tab="edit-{{ $fighter->getKey() }}-boxing-bouts">Aktuelle Kampfzahl</a>
                                                            <a href="#fighter-boxing-panel-edit-{{ $fighter->getKey() }}-boxing-pass" id="fighter-boxing-tab-edit-{{ $fighter->getKey() }}-boxing-pass" role="tab" aria-selected="false" aria-controls="fighter-boxing-panel-edit-{{ $fighter->getKey() }}-boxing-pass" tabindex="-1" class="fighter-boxing-tab-btn" data-boxing-tab="edit-{{ $fighter->getKey() }}-boxing-pass">Kampfpass</a>
                                                        </div>

                                                        <div id="fighter-boxing-panel-edit-{{ $fighter->getKey() }}-boxing-weight" role="tabpanel" aria-labelledby="fighter-boxing-tab-edit-{{ $fighter->getKey() }}-boxing-weight" data-boxing-tab-panel="edit-{{ $fighter->getKey() }}-boxing-weight" style="display:block;">
                                                            <div data-boxing-row-container="weight" style="display:grid; gap:8px;">
                                                                @php $weightRows = count($fighterWeightEntries) > 0 ? $fighterWeightEntries : [['date' => null, 'weight_kg' => null]]; @endphp
                                                                @foreach ($weightRows as $weightRow)
                                                                    <div data-boxing-row style="display:grid; grid-template-columns:180px 180px auto; gap:8px;">
                                                                        <input type="date" name="boxing_weight_dates[]" value="{{ (string) ($weightRow['date'] ?? '') }}">
                                                                        <input type="number" step="0.01" min="0" name="boxing_weight_values[]" value="{{ (string) ($weightRow['weight_kg'] ?? '') }}" placeholder="kg">
                                                                        <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <div style="margin-top:8px;">
                                                                <button type="button" class="btn btn-soft" data-add-boxing-row="weight">Zeile hinzufuegen</button>
                                                            </div>
                                                        </div>

                                                        <div id="fighter-boxing-panel-edit-{{ $fighter->getKey() }}-boxing-bouts" role="tabpanel" aria-labelledby="fighter-boxing-tab-edit-{{ $fighter->getKey() }}-boxing-bouts" data-boxing-tab-panel="edit-{{ $fighter->getKey() }}-boxing-bouts" hidden style="display:none;">
                                                            <div data-boxing-row-container="bouts" style="display:grid; gap:8px;">
                                                                @php $boutRows = count($fighterBoutEntries) > 0 ? $fighterBoutEntries : [['date' => null, 'wins' => null, 'losses' => null, 'draws' => null]]; @endphp
                                                                @foreach ($boutRows as $boutRow)
                                                                    <div data-boxing-row style="display:grid; grid-template-columns:180px repeat(3, 110px) auto; gap:8px;">
                                                                        <input type="date" name="boxing_bout_dates[]" value="{{ (string) ($boutRow['date'] ?? '') }}">
                                                                        <input type="number" min="0" name="boxing_bout_wins[]" value="{{ (string) ($boutRow['wins'] ?? '') }}" placeholder="Siege">
                                                                        <input type="number" min="0" name="boxing_bout_losses[]" value="{{ (string) ($boutRow['losses'] ?? '') }}" placeholder="Niederlagen">
                                                                        <input type="number" min="0" name="boxing_bout_draws[]" value="{{ (string) ($boutRow['draws'] ?? '') }}" placeholder="Unentschieden">
                                                                        <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <div style="margin-top:8px;">
                                                                <button type="button" class="btn btn-soft" data-add-boxing-row="bouts">Zeile hinzufuegen</button>
                                                            </div>
                                                        </div>

                                                        <div id="fighter-boxing-panel-edit-{{ $fighter->getKey() }}-boxing-pass" role="tabpanel" aria-labelledby="fighter-boxing-tab-edit-{{ $fighter->getKey() }}-boxing-pass" data-boxing-tab-panel="edit-{{ $fighter->getKey() }}-boxing-pass" hidden style="display:none;">
                                                            <div data-boxing-row-container="pass" style="display:grid; gap:8px;">
                                                                @php
                                                                    $passRows = count($fighterPassEntries) > 0
                                                                        ? $fighterPassEntries
                                                                        : array_map(fn ($keyword) => ['keyword' => $keyword, 'date' => null], $boxingPassKeywords);
                                                                    if (count($passRows) === 0) {
                                                                        $passRows = [['keyword' => '', 'date' => null]];
                                                                    }
                                                                @endphp
                                                                @foreach ($passRows as $passRow)
                                                                    <div data-boxing-row style="display:grid; grid-template-columns:1.4fr 180px auto; gap:8px;">
                                                                        <input name="boxing_pass_keywords[]" value="{{ (string) ($passRow['keyword'] ?? '') }}" placeholder="Stichwort">
                                                                        <input type="date" name="boxing_pass_dates[]" value="{{ (string) ($passRow['date'] ?? '') }}">
                                                                        <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <div style="margin-top:8px;">
                                                                <button type="button" class="btn btn-soft" data-add-boxing-row="pass">Zeile hinzufuegen</button>
                                                            </div>
                                                        </div>

                                                        <template data-boxing-row-template="weight">
                                                            <div data-boxing-row style="display:grid; grid-template-columns:180px 180px auto; gap:8px; margin-top:8px;">
                                                                <input type="date" name="boxing_weight_dates[]">
                                                                <input type="number" step="0.01" min="0" name="boxing_weight_values[]" placeholder="kg">
                                                                <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                            </div>
                                                        </template>
                                                        <template data-boxing-row-template="bouts">
                                                            <div data-boxing-row style="display:grid; grid-template-columns:180px repeat(3, 110px) auto; gap:8px; margin-top:8px;">
                                                                <input type="date" name="boxing_bout_dates[]">
                                                                <input type="number" min="0" name="boxing_bout_wins[]" placeholder="Siege">
                                                                <input type="number" min="0" name="boxing_bout_losses[]" placeholder="Niederlagen">
                                                                <input type="number" min="0" name="boxing_bout_draws[]" placeholder="Unentschieden">
                                                                <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                            </div>
                                                        </template>
                                                        <template data-boxing-row-template="pass">
                                                            <div data-boxing-row style="display:grid; grid-template-columns:1.4fr 180px auto; gap:8px; margin-top:8px;">
                                                                <input name="boxing_pass_keywords[]" placeholder="Stichwort">
                                                                <input type="date" name="boxing_pass_dates[]">
                                                                <button type="button" class="btn btn-soft" data-remove-boxing-row>Entfernen</button>
                                                            </div>
                                                        </template>
                                                    @else
                                                        <strong>{{ $moduleName }}</strong>
                                                        <div class="muted" style="margin-top:6px;">Sportartspezifische Details für dieses Modul folgen.</div>
                                                    @endif
                                                </section>
                                            @endif
                                        @endforeach

                                        <div style="margin-top:14px; display:flex; justify-content:flex-end;">
                                            <button class="btn" type="submit">Speichern</button>
                                        </div>
                                    </form>
                                </dialog>
                            @endif
                        @empty
                            <div class="row">Keine Kämpfer vorhanden.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'trainers')
                    <h2 class="section-title">Trainer und Verantwortliche</h2>
                    <div class="list">
                        @forelse ($trainers as $trainer)
                            <article class="row">
                                <div>
                                    <strong>{{ $trainer->name }}</strong>
                                    <div class="muted" style="margin-top:4px;">{{ $trainer->email }}</div>
                                </div>
                                <span class="pill">{{ $trainer->role }}</span>
                            </article>
                        @empty
                            <div class="row">Keine Trainer gefunden.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'events')
                    <h2 class="section-title">Veranstaltungen</h2>

                    @php
                        $activeSportModules = (array) ($activeSportModules ?? []);
                        $eventSportModules = array_values(array_filter($activeSportModules, function (array $module): bool {
                            $moduleSlug = (string) ($module['slug'] ?? '');
                            return in_array($moduleSlug, ['boxing'], true);
                        }));
                        $eventSportModuleSlugs = array_map(fn (array $module): string => (string) ($module['slug'] ?? ''), $eventSportModules);
                        $defaultCreateSportModule = old('sport_module', in_array('boxing', $eventSportModuleSlugs, true) ? 'boxing' : ($eventSportModuleSlugs[0] ?? ''));
                        $boxingEnabled = count($boxingPackages ?? []) > 0;
                        $boxingPackageKeys = array_keys((array) ($boxingPackages ?? []));
                        $defaultCreateBoxingPackage = old('boxing_package_key', $boxingActivePackage ?: ($boxingPackageKeys[0] ?? ''));
                        $formatEventFeeAmount = static function ($cents): string {
                            return is_numeric($cents) ? number_format(((int) $cents) / 100, 2, '.', '') : '';
                        };
                        $eventStatusOptions = [
                            'draft' => 'Entwurf',
                            'published' => 'Veröffentlicht',
                            'cancelled' => 'Abgesagt',
                        ];
                    @endphp

                    @if ($canManageEvents)
                        <div style="margin-bottom:12px; display:flex; justify-content:space-between; gap:10px; align-items:center; flex-wrap:wrap;">
                            @if ($isAiModuleReady)
                                <form method="post" action="{{ route('clubs.events.ai.extract', $club) }}" enctype="multipart/form-data" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                    @csrf
                                    <input type="file" name="event_pdf" accept="application/pdf" required>
                                    <button type="submit" class="btn btn-soft">Aus PDF erstellen (KI)</button>
                                </form>
                            @endif

                            <button type="button" class="btn" data-open-modal="create-event-modal">Leere Veranstaltung erstellen</button>
                        </div>

                        <dialog id="create-event-modal" style="width:min(1080px, calc(100% - 24px)); border:1px solid var(--line); border-radius:14px; padding:0;">
                            <form method="dialog" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid var(--line); background:#f7faf5;">
                                <strong>Neue Veranstaltung</strong>
                                <button class="btn btn-soft" type="submit">Schließen</button>
                            </form>
                            @php
                                $createEntryFeeAmount = old('entry_fee_amount');
                                if (($createEntryFeeAmount === null || $createEntryFeeAmount === '') && old('entry_fee_cents') !== null) {
                                    $createEntryFeeAmount = $formatEventFeeAmount(old('entry_fee_cents'));
                                }
                            @endphp
                            <form method="post" action="{{ route('clubs.events.store', $club) }}" enctype="multipart/form-data" class="event-modal-form" data-event-form="create" data-default-event-tab="master-data">
                                @csrf
                                <input type="hidden" name="open_create_event_modal" value="1">
                                <input type="hidden" name="ai_original_pdf_path" value="{{ old('ai_original_pdf_path') }}">
                                <input type="hidden" name="location" value="{{ old('location') }}">
                                <input type="hidden" name="address_line2" value="{{ old('address_line2') }}">
                                <input type="hidden" name="country" value="{{ old('country', 'DE') }}">

                                <div class="event-modal-topline">
                                    <div style="display:grid; gap:10px; flex:1; min-width:280px;">
                                        <div class="event-organizer-note">Veranstalter: {{ $club->name }}</div>
                                        <div class="form-row">
                                            <label>Sportart</label>
                                            <div class="option-pill-grid">
                                                @foreach ($eventSportModules as $module)
                                                    @php
                                                        $moduleSlug = (string) ($module['slug'] ?? '');
                                                        $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                                    @endphp
                                                    @if ($moduleSlug !== '')
                                                        <label class="option-pill" data-kind="sport">
                                                            <input type="radio" name="sport_module" value="{{ $moduleSlug }}" @checked($defaultCreateSportModule === $moduleSlug) required>
                                                            <span>{{ $moduleName }}</span>
                                                        </label>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="event-status-card">
                                        <div class="form-row">
                                            <label for="event_status_create">Status</label>
                                            <select id="event_status_create" name="status" data-summary-source="status">
                                                @foreach ($eventStatusOptions as $statusKey => $statusLabel)
                                                    <option value="{{ $statusKey }}" @selected(old('status', 'draft') === $statusKey)>{{ $statusLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="event-modal-tabs" data-event-tabs role="tablist" aria-label="Veranstaltungsformular">
                                    <a href="#event-tab-panel-create-master-data" id="event-tab-create-master-data" role="tab" aria-selected="true" aria-controls="event-tab-panel-create-master-data" tabindex="0" class="event-modal-tab-link is-active" data-event-tab="master-data">Basics</a>
                                    <a href="#event-tab-panel-create-organisation" id="event-tab-create-organisation" role="tab" aria-selected="false" aria-controls="event-tab-panel-create-organisation" tabindex="-1" class="event-modal-tab-link" data-event-tab="organisation">Organisation</a>
                                    <a href="#event-tab-panel-create-overview" id="event-tab-create-overview" role="tab" aria-selected="false" aria-controls="event-tab-panel-create-overview" tabindex="-1" class="event-modal-tab-link" data-event-tab="overview">Übersicht</a>
                                </div>

                                <section id="event-tab-panel-create-master-data" class="event-tab-panel is-active" role="tabpanel" aria-labelledby="event-tab-create-master-data" data-event-tab-panel="master-data">
                                    <div class="event-section-card">
                                        <h3 class="event-section-title">Veranstaltungs-Basics</h3>
                                        <div class="form-row">
                                            <label for="title_create_event">Titel</label>
                                            <input id="title_create_event" name="title" value="{{ old('title') }}" required data-summary-source="title">
                                        </div>
                                        <div class="form-grid">
                                            <div class="form-row">
                                                <label for="starts_at_create_event">Start</label>
                                                <input id="starts_at_create_event" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required data-summary-source="starts_at">
                                            </div>
                                            <div class="form-row">
                                                <label for="ends_at_create_event">Ende</label>
                                                <input id="ends_at_create_event" type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" data-summary-source="ends_at">
                                            </div>
                                            <div class="form-row">
                                                <label for="registration_deadline_create_event">Anmeldeschluss</label>
                                                <input id="registration_deadline_create_event" type="datetime-local" name="registration_deadline" value="{{ old('registration_deadline') }}" data-summary-source="registration_deadline">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="event-section-card">
                                        <h3 class="event-section-title">Adresse</h3>
                                        <div class="event-section-note">Hallenname, Straße, PLZ und Ort in einer kompakten Anschrift.</div>
                                        <div class="address-grid">
                                            <div class="form-row">
                                                <label for="venue_name_create_event">Hallenname</label>
                                                <input id="venue_name_create_event" name="venue_name" value="{{ old('venue_name') }}" placeholder="z. B. Sporthalle Nord" data-summary-source="venue_name">
                                            </div>
                                            <div class="form-row">
                                                <label for="address_line1_create_event">Straße</label>
                                                <input id="address_line1_create_event" name="address_line1" value="{{ old('address_line1') }}" placeholder="Straße und Hausnummer" data-summary-source="address_line1">
                                            </div>
                                            <div class="form-row">
                                                <label for="postal_code_create_event">PLZ</label>
                                                <input id="postal_code_create_event" name="postal_code" value="{{ old('postal_code') }}" maxlength="50" data-summary-source="postal_code">
                                            </div>
                                            <div class="form-row">
                                                <label for="city_create_event">Ort</label>
                                                <input id="city_create_event" name="city" value="{{ old('city') }}" data-summary-source="city">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="event-section-card">
                                        <h3 class="event-section-title">Gebühr und Hinweise</h3>
                                        <div class="form-grid">
                                            <div class="form-row">
                                                <label for="entry_fee_amount_create_event">Startgebühr</label>
                                                <div class="currency-field">
                                                    <input id="entry_fee_amount_create_event" type="number" step="0.01" min="0" name="entry_fee_amount" value="{{ $createEntryFeeAmount }}" placeholder="0.00" data-summary-source="entry_fee_amount">
                                                    <input name="currency" value="{{ old('currency', 'EUR') }}" maxlength="3" data-summary-source="currency">
                                                </div>
                                            </div>
                                            <div class="form-row" style="grid-column:1 / -1;">
                                                <label for="description_create_event">Beschreibung</label>
                                                <textarea id="description_create_event" name="description" data-summary-source="description">{{ old('description') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section id="event-tab-panel-create-organisation" class="event-tab-panel" role="tabpanel" aria-labelledby="event-tab-create-organisation" data-event-tab-panel="organisation" hidden>
                                    <div class="event-section-card">
                                        <h3 class="event-section-title">Organisation</h3>
                                        <div class="form-grid">
                                            <div class="form-row">
                                                <label for="allow_waitlist_create_event">Warteliste zulassen</label>
                                                <select id="allow_waitlist_create_event" name="allow_waitlist" data-summary-source="allow_waitlist">
                                                    <option value="0" @selected((string) old('allow_waitlist', '0') === '0')>Nein</option>
                                                    <option value="1" @selected((string) old('allow_waitlist') === '1')>Ja</option>
                                                </select>
                                            </div>
                                            <div class="form-row">
                                                <label for="max_registrations_create_event">Max. Teilnehmerzahl</label>
                                                <input id="max_registrations_create_event" type="number" min="1" name="max_registrations" value="{{ old('max_registrations') }}" data-summary-source="max_registrations">
                                            </div>
                                            <div class="form-row">
                                                <label for="registration_approval_mode_create_event">Meldungen sind</label>
                                                <select id="registration_approval_mode_create_event" name="registration_approval_mode" data-summary-source="registration_approval_mode">
                                                    <option value="auto" @selected(old('registration_approval_mode', 'auto') === 'auto')>sofort gültig</option>
                                                    <option value="manual" @selected(old('registration_approval_mode') === 'manual')>erst nach Freigabe gültig</option>
                                                </select>
                                            </div>
                                            <div class="form-row" style="grid-column:1 / -1;">
                                                <label for="event_original_pdf_create">Original-PDF</label>
                                                <input id="event_original_pdf_create" type="file" name="event_original_pdf" accept="application/pdf">
                                            </div>
                                        </div>
                                    </div>

                                    @if ($boxingEnabled)
                                        <div class="event-section-card" data-sport-panel="create-boxing" style="display:{{ $defaultCreateSportModule === 'boxing' ? 'grid' : 'none' }};">
                                            <h3 class="event-section-title">Boxen</h3>
                                            <div class="form-row">
                                                <label for="boxing_package_key_create">Paket</label>
                                                <select id="boxing_package_key_create" name="boxing_package_key" class="boxing-package-select" data-boxing-target="boxing-create">
                                                    @foreach ($boxingPackages as $packageKey => $package)
                                                        <option value="{{ $packageKey }}" @selected($defaultCreateBoxingPackage === $packageKey)>{{ $package['name'] ?? $packageKey }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @foreach ($boxingPackages as $packageKey => $package)
                                                @php
                                                    $ageClasses = (array) ($package['age_classes'] ?? []);
                                                    $ageRanges = (array) (($package['_ui']['age_ranges'] ?? []));
                                                    $performanceClasses = (array) ($package['performance_classes'] ?? []);
                                                    $selectedAgeClasses = (array) old('boxing_age_classes', array_keys($ageClasses));
                                                    $selectedSexes = collect($selectedAgeClasses)
                                                        ->map(fn ($ageCode) => trim((string) (($ageClasses[$ageCode]['sex'] ?? ''))))
                                                        ->filter(fn ($sex) => in_array($sex, ['m', 'w'], true))
                                                        ->unique()
                                                        ->values()
                                                        ->all();
                                                    $selectedPerformanceClasses = (array) old('boxing_performance_classes', array_map(fn (array $class): string => (string) ($class['key'] ?? ''), $performanceClasses));
                                                @endphp
                                                <div data-boxing-package-panel="boxing-create-{{ $packageKey }}" style="display:{{ $defaultCreateBoxingPackage === $packageKey ? 'grid' : 'none' }}; gap:14px;">
                                                    <div class="form-row">
                                                        <label>Leistungsklassen</label>
                                                        <div class="option-pill-grid">
                                                            @foreach ($performanceClasses as $performanceClass)
                                                                @php $performanceKey = (string) ($performanceClass['key'] ?? ''); @endphp
                                                                <label class="option-pill">
                                                                    <input type="checkbox" name="boxing_performance_classes[]" value="{{ $performanceKey }}" @checked(in_array($performanceKey, $selectedPerformanceClasses, true))>
                                                                    <span>{{ $performanceClass['name'] ?? $performanceKey }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="form-row">
                                                        <label>Altersklassen</label>
                                                        <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px;">
                                                            @foreach (['m' => 'Männlich', 'w' => 'Weiblich'] as $columnSex => $columnLabel)
                                                                <div style="display:grid; gap:8px;">
                                                                    <div class="muted" style="font-weight:700;">{{ $columnLabel }}</div>
                                                                    <div class="option-pill-grid" style="align-items:flex-start;">
                                                                        @foreach ($ageClasses as $ageCode => $ageData)
                                                                            @php
                                                                                $sexKey = trim((string) ($ageData['sex'] ?? ''));
                                                                                $ageName = (string) ($ageData['name'] ?? $ageCode);
                                                                                $ageRange = (string) ($ageRanges[$ageCode] ?? '');
                                                                                $ageWeights = collect((array) ($ageData['gewicht'] ?? []))
                                                                                    ->map(function ($weight, $limit) {
                                                                                        if (! is_array($weight)) {
                                                                                            return null;
                                                                                        }

                                                                                        return [
                                                                                            'short' => (string) ($weight['short'] ?? $limit),
                                                                                            'name' => (string) ($weight['name'] ?? $limit),
                                                                                        ];
                                                                                    })
                                                                                    ->filter(fn ($row) => is_array($row))
                                                                                    ->values()
                                                                                    ->all();
                                                                            @endphp
                                                                            @if ($sexKey === $columnSex)
                                                                                <label class="option-pill boxing-age-class-pill">
                                                                                    <input
                                                                                        type="checkbox"
                                                                                        class="boxing-age-class-toggle"
                                                                                        name="boxing_age_classes[]"
                                                                                        value="{{ $ageCode }}"
                                                                                        data-age-code="{{ $ageCode }}"
                                                                                        data-age-sex="{{ $sexKey }}"
                                                                                        data-age-name="{{ $ageName }}"
                                                                                        data-age-range="{{ $ageRange }}"
                                                                                        data-age-weights='@json($ageWeights)'
                                                                                        @checked(in_array($ageCode, $selectedAgeClasses, true))
                                                                                    >
                                                                                    <span>{{ $ageName }}{{ $ageRange !== '' ? (' (' . $ageRange . ')') : '' }}</span>
                                                                                </label>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div style="display:none;">
                                                            <input type="checkbox" name="boxing_sexes[]" value="m" data-boxing-derived-sex="m" @checked(in_array('m', $selectedSexes, true))>
                                                            <input type="checkbox" name="boxing_sexes[]" value="w" data-boxing-derived-sex="w" @checked(in_array('w', $selectedSexes, true))>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @foreach ($eventSportModules as $module)
                                        @php
                                            $moduleSlug = (string) ($module['slug'] ?? '');
                                            $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                        @endphp
                                        @if ($moduleSlug !== '' && $moduleSlug !== 'boxing')
                                            <div class="event-section-card" data-sport-panel="create-{{ $moduleSlug }}" style="display:{{ $defaultCreateSportModule === $moduleSlug ? 'grid' : 'none' }};">
                                                <h3 class="event-section-title">{{ $moduleName }}</h3>
                                                <div class="event-section-note">Sportartspezifische Einstellungen folgen für dieses Modul.</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </section>

                                <section id="event-tab-panel-create-overview" class="event-tab-panel" role="tabpanel" aria-labelledby="event-tab-create-overview" data-event-tab-panel="overview" hidden>
                                    <div class="event-summary-grid">
                                        <div class="event-summary-card">
                                            <div class="event-summary-label">Veranstaltung</div>
                                            <div class="event-summary-value" data-summary-field="title">{{ old('title') ?: 'Noch kein Titel' }}</div>
                                            <div class="muted" data-summary-field="sport_module">{{ $defaultCreateSportModule !== '' ? strtoupper($defaultCreateSportModule) : 'Keine Sportart gewählt' }}</div>
                                            <div class="muted" data-summary-field="status">{{ $eventStatusOptions[old('status', 'draft')] ?? 'Entwurf' }}</div>
                                        </div>
                                        <div class="event-summary-card">
                                            <div class="event-summary-label">Zeitplan</div>
                                            <div class="event-summary-value" data-summary-field="starts_at">{{ old('starts_at') ?: 'Kein Start gesetzt' }}</div>
                                            <div class="muted">Ende: <span data-summary-field="ends_at">{{ old('ends_at') ?: 'offen' }}</span></div>
                                            <div class="muted">Anmeldeschluss: <span data-summary-field="registration_deadline">{{ old('registration_deadline') ?: 'offen' }}</span></div>
                                        </div>
                                        <div class="event-summary-card">
                                            <div class="event-summary-label">Ort & Gebühr</div>
                                            <div class="event-summary-value" data-summary-field="address">{{ trim(implode(', ', array_filter([old('venue_name'), old('address_line1'), trim(((string) old('postal_code')) . ' ' . ((string) old('city')))]))) ?: 'Adresse noch unvollständig' }}</div>
                                            <div class="muted">Startgebühr: <span data-summary-field="entry_fee">{{ $createEntryFeeAmount !== null && $createEntryFeeAmount !== '' ? $createEntryFeeAmount . ' ' . old('currency', 'EUR') : 'offen' }}</span></div>
                                            <div class="muted">Warteliste: <span data-summary-field="allow_waitlist">{{ (string) old('allow_waitlist', '0') === '1' ? 'Ja' : 'Nein' }}</span></div>
                                            <div class="muted">Meldungen: <span data-summary-field="registration_approval_mode">{{ old('registration_approval_mode', 'auto') === 'manual' ? 'mit Freigabe' : 'sofort gültig' }}</span></div>
                                        </div>
                                        <div class="event-summary-card">
                                            <div class="event-summary-label">Box-Klassen</div>
                                            <div>
                                                <div class="muted" style="margin-bottom:6px;">Geschlechter</div>
                                                <div class="event-summary-pills" data-summary-list="boxing_sexes"></div>
                                            </div>
                                            <div>
                                                <div class="muted" style="margin-bottom:6px;">Altersklassen (echtes Alter)</div>
                                                <div class="event-age-selector" data-summary-age-selector role="tablist" aria-label="Altersklassen"></div>
                                                <div class="muted" data-summary-selected-age>Keine Altersklasse ausgewählt.</div>
                                                <ul class="event-weight-list" data-summary-weight-list></ul>
                                            </div>
                                            <div>
                                                <div class="muted" style="margin-bottom:6px;">Leistungsklassen</div>
                                                <div class="event-summary-pills" data-summary-list="boxing_performance_classes"></div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <div style="display:flex; justify-content:flex-end;">
                                    <button class="btn" type="submit">Speichern</button>
                                </div>
                            </form>
                        </dialog>
                    @endif

                    <div class="list">
                        @forelse ($clubEvents as $clubEvent)
                            <article class="row" style="align-items:center;">
                                <div>
                                    <strong>{{ $clubEvent->title }}</strong>
                                    <div class="muted" style="margin-top:4px;">{{ $clubEvent->starts_at->format('d.m.Y H:i') }}</div>
                                    <div class="muted" style="margin-top:2px;">Gemeldete Kämpfer: {{ (int) ($clubEvent->registered_fighters_count ?? 0) }}</div>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    @if ($clubEvent->sport_module)
                                        <span class="pill">{{ strtoupper((string) $clubEvent->sport_module) }}</span>
                                    @endif
                                    <span class="pill">{{ strtoupper($clubEvent->status) }}</span>
                                        <span class="pill">{{ ($clubEvent->registration_approval_mode ?? 'auto') === 'manual' ? 'FREIGABE' : 'AUTO' }}</span>
                                    @if ($canManageEvents && $isAiModuleReady)
                                        <form method="post" action="{{ route('clubs.events.ai.pairings', ['club' => $club, 'event' => $clubEvent]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-soft">KI-Paarungen vorschlagen</button>
                                        </form>
                                    @endif
                                    @if ($canManageEvents)
                                        <button type="button" class="btn btn-soft" data-open-modal="edit-event-modal-{{ $clubEvent->getKey() }}">Bearbeiten</button>
                                    @endif
                                </div>
                            </article>

                            @if ($canManageEvents)
                                @php
                                    $defaultUpdateSportModule = old('sport_module', $clubEvent->sport_module ?: (in_array('boxing', $eventSportModuleSlugs, true) ? 'boxing' : ($eventSportModuleSlugs[0] ?? '')));
                                    $updateBoxingPackageKey = old('boxing_package_key', $clubEvent->boxing_package_key ?: $boxingActivePackage ?: (array_key_first($boxingPackages) ?: ''));
                                    $updateEntryFeeAmount = $formatEventFeeAmount($clubEvent->entry_fee_cents);
                                    $defaultEditEventTab = $activeTab === 'events'
                                        && (int) request()->query('open_event') === (int) $clubEvent->getKey()
                                        && request()->query('event_modal_tab') === 'registrations'
                                            ? 'registrations'
                                            : 'master-data';
                                @endphp
                                <dialog id="edit-event-modal-{{ $clubEvent->getKey() }}" style="width:min(1080px, calc(100% - 24px)); border:1px solid var(--line); border-radius:14px; padding:0;">
                                    <form method="dialog" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid var(--line); background:#f7faf5;">
                                        <strong>Veranstaltung bearbeiten</strong>
                                        <button class="btn btn-soft" type="submit">Schließen</button>
                                    </form>
                                    <form method="post" action="{{ route('clubs.events.update', ['club' => $club, 'event' => $clubEvent]) }}" enctype="multipart/form-data" class="event-modal-form" data-event-form="event-{{ $clubEvent->getKey() }}" data-default-event-tab="{{ $defaultEditEventTab }}">
                                        <input type="hidden" name="tab" value="events">
                                        <input type="hidden" name="location" value="{{ $clubEvent->location }}">
                                        <input type="hidden" name="address_line2" value="{{ $clubEvent->address_line2 }}">
                                        <input type="hidden" name="country" value="{{ $clubEvent->country ?? 'DE' }}">
                                        @csrf
                                        @method('patch')
                                        <div class="event-modal-topline">
                                            <div style="display:grid; gap:10px; flex:1; min-width:280px;">
                                                <div class="event-organizer-note">Veranstalter: {{ $club->name }}</div>
                                                <div class="form-row">
                                                    <label>Sportart</label>
                                                    <div class="option-pill-grid">
                                                        @foreach ($eventSportModules as $module)
                                                            @php
                                                                $moduleSlug = (string) ($module['slug'] ?? '');
                                                                $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                                            @endphp
                                                            @if ($moduleSlug !== '')
                                                                <label class="option-pill" data-kind="sport">
                                                                    <input type="radio" name="sport_module" value="{{ $moduleSlug }}" @checked($defaultUpdateSportModule === $moduleSlug) required>
                                                                    <span>{{ $moduleName }}</span>
                                                                </label>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="event-status-card">
                                                <div class="form-row">
                                                    <label for="event_status_{{ $clubEvent->getKey() }}">Status</label>
                                                    <select id="event_status_{{ $clubEvent->getKey() }}" name="status" data-summary-source="status">
                                                        @foreach ($eventStatusOptions as $statusKey => $statusLabel)
                                                            <option value="{{ $statusKey }}" @selected($clubEvent->status === $statusKey)>{{ $statusLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="event-modal-tabs" data-event-tabs role="tablist" aria-label="Veranstaltungsformular">
                                            <a href="#event-tab-panel-edit-{{ $clubEvent->getKey() }}-master-data" id="event-tab-edit-{{ $clubEvent->getKey() }}-master-data" role="tab" aria-selected="true" aria-controls="event-tab-panel-edit-{{ $clubEvent->getKey() }}-master-data" tabindex="0" class="event-modal-tab-link is-active" data-event-tab="master-data">Basics</a>
                                            <a href="#event-tab-panel-edit-{{ $clubEvent->getKey() }}-organisation" id="event-tab-edit-{{ $clubEvent->getKey() }}-organisation" role="tab" aria-selected="false" aria-controls="event-tab-panel-edit-{{ $clubEvent->getKey() }}-organisation" tabindex="-1" class="event-modal-tab-link" data-event-tab="organisation">Organisation</a>
                                            <a href="#event-tab-panel-edit-{{ $clubEvent->getKey() }}-overview" id="event-tab-edit-{{ $clubEvent->getKey() }}-overview" role="tab" aria-selected="false" aria-controls="event-tab-panel-edit-{{ $clubEvent->getKey() }}-overview" tabindex="-1" class="event-modal-tab-link" data-event-tab="overview">Übersicht</a>
                                            <a href="#event-tab-panel-edit-{{ $clubEvent->getKey() }}-registrations" id="event-tab-edit-{{ $clubEvent->getKey() }}-registrations" role="tab" aria-selected="false" aria-controls="event-tab-panel-edit-{{ $clubEvent->getKey() }}-registrations" tabindex="-1" class="event-modal-tab-link" data-event-tab="registrations">Meldungen</a>
                                        </div>

                                        <section id="event-tab-panel-edit-{{ $clubEvent->getKey() }}-master-data" class="event-tab-panel is-active" role="tabpanel" aria-labelledby="event-tab-edit-{{ $clubEvent->getKey() }}-master-data" data-event-tab-panel="master-data">
                                            <div class="event-section-card">
                                                <h3 class="event-section-title">Veranstaltungs-Basics</h3>
                                                <div class="form-row">
                                                    <label for="event_title_{{ $clubEvent->getKey() }}">Titel</label>
                                                    <input id="event_title_{{ $clubEvent->getKey() }}" name="title" value="{{ $clubEvent->title }}" required data-summary-source="title">
                                                </div>
                                                <div class="form-grid">
                                                    <div class="form-row">
                                                        <label for="event_starts_at_{{ $clubEvent->getKey() }}">Start</label>
                                                        <input id="event_starts_at_{{ $clubEvent->getKey() }}" type="datetime-local" name="starts_at" value="{{ $clubEvent->starts_at?->format('Y-m-d\\TH:i') }}" required data-summary-source="starts_at">
                                                    </div>
                                                    <div class="form-row">
                                                        <label for="event_ends_at_{{ $clubEvent->getKey() }}">Ende</label>
                                                        <input id="event_ends_at_{{ $clubEvent->getKey() }}" type="datetime-local" name="ends_at" value="{{ $clubEvent->ends_at?->format('Y-m-d\\TH:i') }}" data-summary-source="ends_at">
                                                    </div>
                                                    <div class="form-row">
                                                        <label for="event_deadline_{{ $clubEvent->getKey() }}">Anmeldeschluss</label>
                                                        <input id="event_deadline_{{ $clubEvent->getKey() }}" type="datetime-local" name="registration_deadline" value="{{ $clubEvent->registration_deadline?->format('Y-m-d\\TH:i') }}" data-summary-source="registration_deadline">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="event-section-card">
                                                <h3 class="event-section-title">Adresse</h3>
                                                <div class="event-section-note">Hallenname, Straße, PLZ und Ort in einer kompakten Anschrift.</div>
                                                <div class="address-grid">
                                                    <div class="form-row">
                                                        <label for="event_venue_{{ $clubEvent->getKey() }}">Hallenname</label>
                                                        <input id="event_venue_{{ $clubEvent->getKey() }}" name="venue_name" value="{{ $clubEvent->venue_name }}" data-summary-source="venue_name">
                                                    </div>
                                                    <div class="form-row">
                                                        <label for="event_address_{{ $clubEvent->getKey() }}">Straße</label>
                                                        <input id="event_address_{{ $clubEvent->getKey() }}" name="address_line1" value="{{ $clubEvent->address_line1 }}" data-summary-source="address_line1">
                                                    </div>
                                                    <div class="form-row">
                                                        <label for="event_postal_{{ $clubEvent->getKey() }}">PLZ</label>
                                                        <input id="event_postal_{{ $clubEvent->getKey() }}" name="postal_code" value="{{ $clubEvent->postal_code }}" data-summary-source="postal_code">
                                                    </div>
                                                    <div class="form-row">
                                                        <label for="event_city_{{ $clubEvent->getKey() }}">Ort</label>
                                                        <input id="event_city_{{ $clubEvent->getKey() }}" name="city" value="{{ $clubEvent->city }}" data-summary-source="city">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="event-section-card">
                                                <h3 class="event-section-title">Gebühr und Hinweise</h3>
                                                <div class="form-grid">
                                                    <div class="form-row">
                                                        <label for="event_fee_{{ $clubEvent->getKey() }}">Startgebühr</label>
                                                        <div class="currency-field">
                                                            <input id="event_fee_{{ $clubEvent->getKey() }}" type="number" step="0.01" min="0" name="entry_fee_amount" value="{{ $updateEntryFeeAmount }}" data-summary-source="entry_fee_amount">
                                                            <input name="currency" value="{{ $clubEvent->currency ?? 'EUR' }}" maxlength="3" data-summary-source="currency">
                                                        </div>
                                                    </div>
                                                    <div class="form-row" style="grid-column:1 / -1;">
                                                        <label for="event_description_{{ $clubEvent->getKey() }}">Beschreibung</label>
                                                        <textarea id="event_description_{{ $clubEvent->getKey() }}" name="description" data-summary-source="description">{{ $clubEvent->description }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <section id="event-tab-panel-edit-{{ $clubEvent->getKey() }}-organisation" class="event-tab-panel" role="tabpanel" aria-labelledby="event-tab-edit-{{ $clubEvent->getKey() }}-organisation" data-event-tab-panel="organisation" hidden>
                                            <div class="event-section-card">
                                                <h3 class="event-section-title">Organisation</h3>
                                                <div class="form-grid">
                                                    <div class="form-row">
                                                        <label for="event_waitlist_{{ $clubEvent->getKey() }}">Warteliste zulassen</label>
                                                        <select id="event_waitlist_{{ $clubEvent->getKey() }}" name="allow_waitlist" data-summary-source="allow_waitlist">
                                                            <option value="0" @selected(! $clubEvent->allow_waitlist)>Nein</option>
                                                            <option value="1" @selected((bool) $clubEvent->allow_waitlist)>Ja</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-row">
                                                        <label for="event_max_{{ $clubEvent->getKey() }}">Max. Teilnehmerzahl</label>
                                                        <input id="event_max_{{ $clubEvent->getKey() }}" type="number" min="1" name="max_registrations" value="{{ $clubEvent->max_registrations }}" data-summary-source="max_registrations">
                                                    </div>
                                                    <div class="form-row">
                                                        <label for="event_approval_{{ $clubEvent->getKey() }}">Meldungen sind</label>
                                                        <select id="event_approval_{{ $clubEvent->getKey() }}" name="registration_approval_mode" data-summary-source="registration_approval_mode">
                                                            <option value="auto" @selected(($clubEvent->registration_approval_mode ?? 'auto') === 'auto')>sofort gültig</option>
                                                            <option value="manual" @selected(($clubEvent->registration_approval_mode ?? 'auto') === 'manual')>erst nach Freigabe gültig</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-row" style="grid-column:1 / -1;">
                                                        <label for="event_pdf_{{ $clubEvent->getKey() }}">Original-PDF (optional anhängen)</label>
                                                        <input id="event_pdf_{{ $clubEvent->getKey() }}" type="file" name="event_original_pdf" accept="application/pdf">
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($boxingEnabled)
                                                <div class="event-section-card" data-sport-panel="event-{{ $clubEvent->getKey() }}-boxing" style="display:{{ $defaultUpdateSportModule === 'boxing' ? 'grid' : 'none' }};">
                                                    <h3 class="event-section-title">Boxen</h3>
                                                    <div class="form-row">
                                                        <label for="event_package_{{ $clubEvent->getKey() }}">Paket</label>
                                                        <select id="event_package_{{ $clubEvent->getKey() }}" name="boxing_package_key" class="boxing-package-select" data-boxing-target="boxing-event-{{ $clubEvent->getKey() }}">
                                                            @foreach ($boxingPackages as $packageKey => $package)
                                                                <option value="{{ $packageKey }}" @selected($updateBoxingPackageKey === $packageKey)>{{ $package['name'] ?? $packageKey }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    @foreach ($boxingPackages as $packageKey => $package)
                                                        @php
                                                            $ageClasses = (array) ($package['age_classes'] ?? []);
                                                            $ageRanges = (array) (($package['_ui']['age_ranges'] ?? []));
                                                            $performanceClasses = (array) ($package['performance_classes'] ?? []);
                                                            $selectedAgeClasses = (array) ($clubEvent->boxing_age_classes ?: array_keys($ageClasses));
                                                            $selectedSexes = collect($selectedAgeClasses)
                                                                ->map(fn ($ageCode) => trim((string) (($ageClasses[$ageCode]['sex'] ?? ''))))
                                                                ->filter(fn ($sex) => in_array($sex, ['m', 'w'], true))
                                                                ->unique()
                                                                ->values()
                                                                ->all();
                                                            $selectedPerformanceClasses = (array) ($clubEvent->boxing_performance_classes ?: array_map(fn (array $class): string => (string) ($class['key'] ?? ''), $performanceClasses));
                                                        @endphp
                                                        <div data-boxing-package-panel="boxing-event-{{ $clubEvent->getKey() }}-{{ $packageKey }}" style="display:{{ $updateBoxingPackageKey === $packageKey ? 'grid' : 'none' }}; gap:14px;">
                                                            <div class="form-row">
                                                                <label>Leistungsklassen</label>
                                                                <div class="option-pill-grid">
                                                                    @foreach ($performanceClasses as $performanceClass)
                                                                        @php $performanceKey = (string) ($performanceClass['key'] ?? ''); @endphp
                                                                        <label class="option-pill">
                                                                            <input type="checkbox" name="boxing_performance_classes[]" value="{{ $performanceKey }}" @checked(in_array($performanceKey, $selectedPerformanceClasses, true))>
                                                                            <span>{{ $performanceClass['name'] ?? $performanceKey }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            <div class="form-row">
                                                                <label>Altersklassen</label>
                                                                <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px;">
                                                                    @foreach (['m' => 'Männlich', 'w' => 'Weiblich'] as $columnSex => $columnLabel)
                                                                        <div style="display:grid; gap:8px;">
                                                                            <div class="muted" style="font-weight:700;">{{ $columnLabel }}</div>
                                                                            <div class="option-pill-grid" style="align-items:flex-start;">
                                                                                @foreach ($ageClasses as $ageCode => $ageData)
                                                                                    @php
                                                                                        $sexKey = trim((string) ($ageData['sex'] ?? ''));
                                                                                        $ageName = (string) ($ageData['name'] ?? $ageCode);
                                                                                        $ageRange = (string) ($ageRanges[$ageCode] ?? '');
                                                                                        $ageWeights = collect((array) ($ageData['gewicht'] ?? []))
                                                                                            ->map(function ($weight, $limit) {
                                                                                                if (! is_array($weight)) {
                                                                                                    return null;
                                                                                                }

                                                                                                return [
                                                                                                    'short' => (string) ($weight['short'] ?? $limit),
                                                                                                    'name' => (string) ($weight['name'] ?? $limit),
                                                                                                ];
                                                                                            })
                                                                                            ->filter(fn ($row) => is_array($row))
                                                                                            ->values()
                                                                                            ->all();
                                                                                    @endphp
                                                                                    @if ($sexKey === $columnSex)
                                                                                        <label class="option-pill boxing-age-class-pill">
                                                                                            <input
                                                                                                type="checkbox"
                                                                                                class="boxing-age-class-toggle"
                                                                                                name="boxing_age_classes[]"
                                                                                                value="{{ $ageCode }}"
                                                                                                data-age-code="{{ $ageCode }}"
                                                                                                data-age-sex="{{ $sexKey }}"
                                                                                                data-age-name="{{ $ageName }}"
                                                                                                data-age-range="{{ $ageRange }}"
                                                                                                data-age-weights='@json($ageWeights)'
                                                                                                @checked(in_array($ageCode, $selectedAgeClasses, true))
                                                                                            >
                                                                                            <span>{{ $ageName }}{{ $ageRange !== '' ? (' (' . $ageRange . ')') : '' }}</span>
                                                                                        </label>
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                                <div style="display:none;">
                                                                    <input type="checkbox" name="boxing_sexes[]" value="m" data-boxing-derived-sex="m" @checked(in_array('m', $selectedSexes, true))>
                                                                    <input type="checkbox" name="boxing_sexes[]" value="w" data-boxing-derived-sex="w" @checked(in_array('w', $selectedSexes, true))>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @foreach ($eventSportModules as $module)
                                                @php
                                                    $moduleSlug = (string) ($module['slug'] ?? '');
                                                    $moduleName = (string) ($module['name'] ?? $moduleSlug);
                                                @endphp
                                                @if ($moduleSlug !== '' && $moduleSlug !== 'boxing')
                                                    <div class="event-section-card" data-sport-panel="event-{{ $clubEvent->getKey() }}-{{ $moduleSlug }}" style="display:{{ $defaultUpdateSportModule === $moduleSlug ? 'grid' : 'none' }};">
                                                        <h3 class="event-section-title">{{ $moduleName }}</h3>
                                                        <div class="event-section-note">Sportartspezifische Einstellungen folgen für dieses Modul.</div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </section>

                                        <section id="event-tab-panel-edit-{{ $clubEvent->getKey() }}-overview" class="event-tab-panel" role="tabpanel" aria-labelledby="event-tab-edit-{{ $clubEvent->getKey() }}-overview" data-event-tab-panel="overview" hidden>
                                            <div class="event-summary-grid">
                                                <div class="event-summary-card">
                                                    <div class="event-summary-label">Veranstaltung</div>
                                                    <div class="event-summary-value" data-summary-field="title">{{ $clubEvent->title }}</div>
                                                    <div class="muted" data-summary-field="sport_module">{{ $clubEvent->sport_module ? strtoupper((string) $clubEvent->sport_module) : 'Keine Sportart gewählt' }}</div>
                                                    <div class="muted" data-summary-field="status">{{ $eventStatusOptions[$clubEvent->status] ?? $clubEvent->status }}</div>
                                                </div>
                                                <div class="event-summary-card">
                                                    <div class="event-summary-label">Zeitplan</div>
                                                    <div class="event-summary-value" data-summary-field="starts_at">{{ $clubEvent->starts_at?->format('Y-m-d H:i') ?: 'Kein Start gesetzt' }}</div>
                                                    <div class="muted">Ende: <span data-summary-field="ends_at">{{ $clubEvent->ends_at?->format('Y-m-d H:i') ?: 'offen' }}</span></div>
                                                    <div class="muted">Anmeldeschluss: <span data-summary-field="registration_deadline">{{ $clubEvent->registration_deadline?->format('Y-m-d H:i') ?: 'offen' }}</span></div>
                                                </div>
                                                <div class="event-summary-card">
                                                    <div class="event-summary-label">Ort & Gebühr</div>
                                                    <div class="event-summary-value" data-summary-field="address">{{ trim(implode(', ', array_filter([$clubEvent->venue_name, $clubEvent->address_line1, trim(((string) $clubEvent->postal_code) . ' ' . ((string) $clubEvent->city))]))) ?: 'Adresse noch unvollständig' }}</div>
                                                    <div class="muted">Startgebühr: <span data-summary-field="entry_fee">{{ $updateEntryFeeAmount !== '' ? $updateEntryFeeAmount . ' ' . ($clubEvent->currency ?? 'EUR') : 'offen' }}</span></div>
                                                    <div class="muted">Warteliste: <span data-summary-field="allow_waitlist">{{ $clubEvent->allow_waitlist ? 'Ja' : 'Nein' }}</span></div>
                                                    <div class="muted">Meldungen: <span data-summary-field="registration_approval_mode">{{ ($clubEvent->registration_approval_mode ?? 'auto') === 'manual' ? 'mit Freigabe' : 'sofort gültig' }}</span></div>
                                                </div>
                                                <div class="event-summary-card">
                                                    <div class="event-summary-label">Box-Klassen</div>
                                                    <div>
                                                        <div class="muted" style="margin-bottom:6px;">Geschlechter</div>
                                                        <div class="event-summary-pills" data-summary-list="boxing_sexes"></div>
                                                    </div>
                                                    <div>
                                                        <div class="muted" style="margin-bottom:6px;">Altersklassen (echtes Alter)</div>
                                                        <div class="event-age-selector" data-summary-age-selector role="tablist" aria-label="Altersklassen"></div>
                                                        <div class="muted" data-summary-selected-age>Keine Altersklasse ausgewählt.</div>
                                                        <ul class="event-weight-list" data-summary-weight-list></ul>
                                                    </div>
                                                    <div>
                                                        <div class="muted" style="margin-bottom:6px;">Leistungsklassen</div>
                                                        <div class="event-summary-pills" data-summary-list="boxing_performance_classes"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>

                                        <section id="event-tab-panel-edit-{{ $clubEvent->getKey() }}-registrations" class="event-tab-panel" role="tabpanel" aria-labelledby="event-tab-edit-{{ $clubEvent->getKey() }}-registrations" data-event-tab-panel="registrations" hidden>
                                            <div class="event-section-card">
                                                <h3 class="event-section-title">Meldungen</h3>
                                                <div class="event-section-note">Diese Ansicht wird erst geladen, wenn du den Reiter öffnest. Filter und Sortierung laufen serverseitig pro Veranstaltung.</div>
                                                <div
                                                    data-event-registrations-shell
                                                    data-registrations-url="{{ route('clubs.events.registrations', ['club' => $club, 'event' => $clubEvent]) }}"
                                                    data-filter-status="{{ request()->query('registration_status', 'all') }}"
                                                    data-filter-query="{{ request()->query('registration_q', '') }}"
                                                    data-filter-group="{{ request()->query('registration_group', 'club') }}"
                                                    data-filter-sort="{{ request()->query('registration_sort', 'weight_class') }}"
                                                >
                                                    <div class="registration-loading">Meldungen werden beim Öffnen dieses Reiters geladen.</div>
                                                </div>
                                            </div>
                                        </section>

                                        <div style="display:flex; justify-content:flex-end;">
                                            <button class="btn" type="submit">Speichern</button>
                                        </div>
                                    </form>
                                </dialog>
                            @endif
                        @empty
                            <div class="row">Noch keine Veranstaltungen vorhanden.</div>
                        @endforelse
                    </div>
                @elseif ($activeTab === 'club-data' && $isManager)
                    <h2 class="section-title">Vereinsdaten bearbeiten</h2>
                    <form method="post" action="{{ route('clubs.update', $club) }}">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="tab" value="club-data">
                        <div class="form-grid">
                            <div class="form-row">
                                <label for="name">Vereinsname</label>
                                <input id="name" name="name" value="{{ old('name', $club->name) }}" required>
                            </div>
                            <div class="form-row">
                                <label for="slug">Slug</label>
                                <input id="slug" name="slug" value="{{ old('slug', $club->slug) }}" required>
                            </div>
                            <div class="form-row" style="grid-column: 1 / -1;">
                                <label for="description">Beschreibung</label>
                                <textarea id="description" name="description">{{ old('description', $club->description) }}</textarea>
                            </div>
                        </div>
                        <div style="margin-top:12px;">
                            <button class="btn" type="submit">Vereinsdaten speichern</button>
                        </div>
                    </form>
                @elseif ($activeTab === 'billing' && $isManager)
                    <h2 class="section-title">Rechnungsdaten bearbeiten</h2>
                    <form method="post" action="{{ route('clubs.update', $club) }}">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="tab" value="billing">
                        <input type="hidden" name="name" value="{{ old('name', $club->name) }}">
                        <input type="hidden" name="slug" value="{{ old('slug', $club->slug) }}">
                        <input type="hidden" name="description" value="{{ old('description', $club->description) }}">
                        <div class="form-grid">
                            <div class="form-row">
                                <label for="billing_company_name">Firma</label>
                                <input id="billing_company_name" name="billing_company_name" value="{{ old('billing_company_name', $club->billing_company_name) }}">
                            </div>
                            <div class="form-row">
                                <label for="billing_contact_name">Kontakt</label>
                                <input id="billing_contact_name" name="billing_contact_name" value="{{ old('billing_contact_name', $club->billing_contact_name) }}">
                            </div>
                            <div class="form-row">
                                <label for="billing_email">E-Mail</label>
                                <input id="billing_email" type="email" name="billing_email" value="{{ old('billing_email', $club->billing_email) }}">
                            </div>
                            <div class="form-row">
                                <label for="billing_address_line1">Adresse 1</label>
                                <input id="billing_address_line1" name="billing_address_line1" value="{{ old('billing_address_line1', $club->billing_address_line1) }}">
                            </div>
                            <div class="form-row">
                                <label for="billing_address_line2">Adresse 2</label>
                                <input id="billing_address_line2" name="billing_address_line2" value="{{ old('billing_address_line2', $club->billing_address_line2) }}">
                            </div>
                            <div class="form-row">
                                <label for="billing_zip">PLZ</label>
                                <input id="billing_zip" name="billing_zip" value="{{ old('billing_zip', $club->billing_zip) }}">
                            </div>
                            <div class="form-row">
                                <label for="billing_city">Ort</label>
                                <input id="billing_city" name="billing_city" value="{{ old('billing_city', $club->billing_city) }}">
                            </div>
                            <div class="form-row">
                                <label for="billing_country">Land</label>
                                <input id="billing_country" name="billing_country" maxlength="2" value="{{ old('billing_country', $club->billing_country ?? 'DE') }}">
                            </div>
                        </div>
                        <div style="margin-top:12px;">
                            <button class="btn" type="submit">Rechnungsdaten speichern</button>
                        </div>
                    </form>
                @endif
            </div>
        </section>
        <script>
            var openDialog = function (modal) {
                if (!modal) {
                    return;
                }

                if (typeof modal.showModal === 'function') {
                    if (!modal.open) {
                        modal.showModal();
                    }

                    return;
                }

                modal.setAttribute('open', 'open');
            };

            document.querySelectorAll('[data-open-modal]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var modalId = button.getAttribute('data-open-modal');
                    if (!modalId) {
                        return;
                    }

                    var modal = document.getElementById(modalId);
                    if (!modal) {
                        return;
                    }

                    openDialog(modal);
                });
            });

            var autoOpenModalId = '{{ $activeTab === 'fighters' && (($openFighterId ?? 0) > 0) ? ('edit-fighter-modal-' . $openFighterId) : '' }}';
            if (autoOpenModalId !== '') {
                var autoModal = document.getElementById(autoOpenModalId);
                if (autoModal) {
                    openDialog(autoModal);
                }
            }

            var autoOpenCreateEventModal = '{{ $activeTab === 'events' && old('open_create_event_modal') ? '1' : '' }}';
            if (autoOpenCreateEventModal === '1') {
                var createEventModal = document.getElementById('create-event-modal');
                if (createEventModal) {
                    openDialog(createEventModal);
                }
            }

            var autoOpenEventModalId = '{{ $activeTab === 'events' && (int) request()->query('open_event') > 0 ? ('edit-event-modal-' . (int) request()->query('open_event')) : '' }}';
            if (autoOpenEventModalId !== '') {
                var autoEventModal = document.getElementById(autoOpenEventModalId);
                if (autoEventModal) {
                    openDialog(autoEventModal);
                }
            }

            var syncChoicePills = function (scope) {
                if (!scope) {
                    return;
                }

                scope.querySelectorAll('.option-pill').forEach(function (pill) {
                    var input = pill.querySelector('input');
                    pill.classList.toggle('is-active', !!input && input.checked);
                });
            };

            var setSummaryText = function (form, key, value) {
                form.querySelectorAll('[data-summary-field="' + key + '"]').forEach(function (node) {
                    node.textContent = value;
                });
            };

            var fieldValue = function (form, selector) {
                var field = form.querySelector(selector);

                if (!field) {
                    return '';
                }

                return typeof field.value === 'string' ? field.value : '';
            };

            var trimmedFieldValue = function (form, selector) {
                return fieldValue(form, selector).trim();
            };

            var selectedOptionText = function (form, selector) {
                var field = form.querySelector(selector);

                if (!field || !field.selectedOptions || !field.selectedOptions.length) {
                    return '';
                }

                return (field.selectedOptions[0].textContent || '').trim();
            };

            var optionPillLabel = function (input) {
                if (!input) {
                    return '';
                }

                var pill = input.closest('.option-pill');
                var label = pill ? pill.querySelector('span') : null;

                return label ? (label.textContent || '').trim() : '';
            };

            var renderSummaryPills = function (container, values) {
                if (!container) {
                    return;
                }

                container.innerHTML = '';

                if (!values.length) {
                    var empty = document.createElement('span');
                    empty.className = 'event-summary-empty';
                    empty.textContent = 'Keine Auswahl';
                    container.appendChild(empty);
                    return;
                }

                values.forEach(function (value) {
                    var pill = document.createElement('span');
                    pill.className = 'pill';
                    pill.textContent = value;
                    container.appendChild(pill);
                });
            };

            var formatDateTimeValue = function (value, fallback, isStart) {
                if (!value) {
                    return fallback;
                }

                var date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return value.replace('T', ' ');
                }

                var datePart = new Intl.DateTimeFormat('de-DE', {
                    weekday: 'long',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                }).format(date);

                var timePart = new Intl.DateTimeFormat('de-DE', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false,
                }).format(date);

                return isStart ? (datePart + ', ab ' + timePart + ' Uhr') : (datePart + ', ' + timePart + ' Uhr');
            };

            var activeSportModule = function (form) {
                return form.querySelector('input[name="sport_module"]:checked');
            };

            var visibleBoxingPackagePanel = function (form) {
                var visiblePanel = null;

                form.querySelectorAll('[data-boxing-package-panel]').forEach(function (panel) {
                    if (panel.style.display !== 'none' && visiblePanel === null) {
                        visiblePanel = panel;
                    }
                });

                return visiblePanel;
            };

            var syncEventSportPanels = function (form) {
                var target = form.getAttribute('data-event-form') || '';
                var selectedSport = activeSportModule(form);
                var selectedValue = selectedSport ? selectedSport.value : '';

                form.querySelectorAll('[data-sport-panel]').forEach(function (panel) {
                    var panelKey = panel.getAttribute('data-sport-panel') || '';
                    panel.style.display = panelKey === (target + '-' + selectedValue) ? 'grid' : 'none';
                });

                var selectedLabel = selectedSport ? (optionPillLabel(selectedSport) || selectedValue) : 'Keine Sportart gewählt';
                setSummaryText(form, 'sport_module', selectedLabel);
            };

            var syncBoxingAgeClassPanels = function (form) {
                form.querySelectorAll('[data-boxing-package-panel]').forEach(function (panel) {
                    var ageClassInputs = panel.querySelectorAll('.boxing-age-class-toggle');
                    var derivedSexes = { m: false, w: false };

                    ageClassInputs.forEach(function (input) {
                        if (!input.hasAttribute('data-age-enabled')) {
                            input.setAttribute('data-age-enabled', input.checked ? '1' : '0');
                        }

                        var enabled = input.getAttribute('data-age-enabled') === '1';
                        input.checked = enabled;

                        if (enabled) {
                            var sex = input.getAttribute('data-age-sex') || '';
                            if (sex === 'm' || sex === 'w') {
                                derivedSexes[sex] = true;
                            }
                        }
                    });

                    panel.querySelectorAll('input[data-boxing-derived-sex]').forEach(function (sexInput) {
                        var sex = sexInput.getAttribute('data-boxing-derived-sex') || '';
                        sexInput.checked = !!derivedSexes[sex];
                    });
                });
            };

            var applyBoxingAgeClassToggle = function (toggleInput) {
                if (!toggleInput.classList.contains('boxing-age-class-toggle')) {
                    return;
                }

                toggleInput.setAttribute('data-age-enabled', toggleInput.checked ? '1' : '0');
            };

            var syncEventSummary = function (form) {
                var title = trimmedFieldValue(form, 'input[name="title"]') || 'Noch kein Titel';
                var status = selectedOptionText(form, 'select[name="status"]') || 'Entwurf';
                var startsAt = formatDateTimeValue(fieldValue(form, 'input[name="starts_at"]'), 'Kein Start gesetzt', true);
                var endsAt = formatDateTimeValue(fieldValue(form, 'input[name="ends_at"]'), 'offen', false);
                var deadline = formatDateTimeValue(fieldValue(form, 'input[name="registration_deadline"]'), 'offen', false);
                var venueName = trimmedFieldValue(form, 'input[name="venue_name"]');
                var addressLine1 = trimmedFieldValue(form, 'input[name="address_line1"]');
                var postalCode = trimmedFieldValue(form, 'input[name="postal_code"]');
                var city = trimmedFieldValue(form, 'input[name="city"]');
                var amount = trimmedFieldValue(form, 'input[name="entry_fee_amount"]');
                var currency = (trimmedFieldValue(form, 'input[name="currency"]') || 'EUR').toUpperCase();
                var allowWaitlist = fieldValue(form, 'select[name="allow_waitlist"]') === '1' ? 'Ja' : 'Nein';
                var approvalMode = fieldValue(form, 'select[name="registration_approval_mode"]') === 'manual' ? 'mit Freigabe' : 'sofort gültig';
                var maxRegistrations = trimmedFieldValue(form, 'input[name="max_registrations"]');
                var address = [
                    venueName,
                    addressLine1,
                    (postalCode + ' ' + city).trim(),
                ].filter(function (part) {
                    return part !== '';
                }).join(', ');

                setSummaryText(form, 'title', title);
                setSummaryText(form, 'status', status);
                setSummaryText(form, 'starts_at', startsAt);
                setSummaryText(form, 'ends_at', endsAt);
                setSummaryText(form, 'registration_deadline', deadline);
                setSummaryText(form, 'address', address || 'Adresse noch unvollständig');
                setSummaryText(form, 'entry_fee', amount !== '' ? (amount + ' ' + currency) : 'offen');
                setSummaryText(form, 'allow_waitlist', allowWaitlist);
                setSummaryText(form, 'registration_approval_mode', approvalMode);

                if (maxRegistrations !== '') {
                    setSummaryText(form, 'max_registrations', maxRegistrations);
                }

                var boxingPanel = visibleBoxingPackagePanel(form);
                var boxingSelections = {
                    boxing_sexes: [],
                    boxing_performance_classes: [],
                };

                var selectedAges = [];

                if (boxingPanel) {
                    Object.keys(boxingSelections).forEach(function (groupKey) {
                        boxingSelections[groupKey] = Array.prototype.map.call(
                            boxingPanel.querySelectorAll('input[name="' + groupKey + '[]"]:checked'),
                            function (input) {
                                if (groupKey === 'boxing_sexes') {
                                    return input.value === 'm' ? 'Männlich' : (input.value === 'w' ? 'Weiblich' : input.value);
                                }
                                return optionPillLabel(input) || input.value;
                            }
                        ).filter(function (value) {
                            return value && value !== '';
                        });
                    });

                    selectedAges = Array.prototype.map.call(
                        boxingPanel.querySelectorAll('input[name="boxing_age_classes[]"]:checked'),
                        function (input) {
                            var ageName = (input.getAttribute('data-age-name') || input.value || '').trim();
                            var ageRange = (input.getAttribute('data-age-range') || '').trim();
                            var weightPayload = input.getAttribute('data-age-weights') || '[]';
                            var weights = [];

                            try {
                                var parsed = JSON.parse(weightPayload);
                                if (Array.isArray(parsed)) {
                                    weights = parsed;
                                }
                            } catch (_) {
                                weights = [];
                            }

                            return {
                                name: ageName,
                                range: ageRange,
                                weights: weights,
                            };
                        }
                    );
                }

                Object.keys(boxingSelections).forEach(function (groupKey) {
                    renderSummaryPills(form.querySelector('[data-summary-list="' + groupKey + '"]'), boxingSelections[groupKey]);
                });

                var ageSelector = form.querySelector('[data-summary-age-selector]');
                var selectedAgeLabel = form.querySelector('[data-summary-selected-age]');
                var weightList = form.querySelector('[data-summary-weight-list]');

                if (ageSelector && selectedAgeLabel && weightList) {
                    if (!weightList.id) {
                        var formTarget = form.getAttribute('data-event-form') || 'event-summary';
                        weightList.id = 'event-weight-list-' + formTarget;
                    }

                    ageSelector.innerHTML = '';
                    weightList.innerHTML = '';

                    if (!selectedAges.length) {
                        selectedAgeLabel.textContent = 'Keine Altersklasse ausgewählt.';
                        return;
                    }

                    var renderWeightList = function (ageEntry) {
                        selectedAgeLabel.textContent = ageEntry.name + (ageEntry.range ? (' (' + ageEntry.range + ')') : '');
                        weightList.innerHTML = '';

                        if (!ageEntry.weights.length) {
                            var emptyItem = document.createElement('li');
                            emptyItem.textContent = 'Keine Gewichtsklassen hinterlegt.';
                            weightList.appendChild(emptyItem);
                            return;
                        }

                        ageEntry.weights.forEach(function (weightEntry) {
                            var item = document.createElement('li');
                            var shortLabel = (weightEntry && typeof weightEntry.short === 'string') ? weightEntry.short.trim() : '';
                            var nameLabel = (weightEntry && typeof weightEntry.name === 'string') ? weightEntry.name.trim() : '';
                            item.textContent = shortLabel !== '' && nameLabel !== '' ? (shortLabel + ' - ' + nameLabel) : (nameLabel || shortLabel || '-');
                            weightList.appendChild(item);
                        });
                    };

                    selectedAges.forEach(function (ageEntry, index) {
                        var chip = document.createElement('a');
                        chip.href = '#' + weightList.id;
                        chip.className = 'event-age-chip';
                        chip.setAttribute('role', 'tab');
                        chip.setAttribute('aria-controls', weightList.id);
                        chip.setAttribute('aria-selected', index === 0 ? 'true' : 'false');
                        chip.setAttribute('tabindex', index === 0 ? '0' : '-1');
                        chip.textContent = ageEntry.name + (ageEntry.range ? (' (' + ageEntry.range + ')') : '');
                        chip.addEventListener('click', function (event) {
                            event.preventDefault();
                            ageSelector.querySelectorAll('.event-age-chip').forEach(function (chipNode) {
                                chipNode.classList.remove('is-active');
                                chipNode.setAttribute('aria-selected', 'false');
                                chipNode.setAttribute('tabindex', '-1');
                            });

                            chip.classList.add('is-active');
                            chip.setAttribute('aria-selected', 'true');
                            chip.setAttribute('tabindex', '0');
                            renderWeightList(ageEntry);
                        });

                        chip.addEventListener('keydown', function (event) {
                            moveTabFocus(chip, '.event-age-chip', event, function (nextChip) {
                                nextChip.click();
                            });
                        });

                        if (index === 0) {
                            chip.classList.add('is-active');
                        }

                        ageSelector.appendChild(chip);
                    });

                    renderWeightList(selectedAges[0]);
                }
            };

            var buildRegistrationUrl = function (shell, formData) {
                var baseUrl = shell.getAttribute('data-registrations-url') || '';
                if (baseUrl === '') {
                    return '';
                }

                var params = new URLSearchParams();
                if (formData) {
                    formData.forEach(function (value, key) {
                        if (typeof value === 'string' && value !== '') {
                            params.set(key, value);
                        }
                    });
                } else {
                    var filterStatus = shell.getAttribute('data-filter-status') || 'all';
                    var filterQuery = shell.getAttribute('data-filter-query') || '';
                    var filterGroup = shell.getAttribute('data-filter-group') || 'club';
                    var filterSort = shell.getAttribute('data-filter-sort') || 'weight_class';

                    if (filterStatus !== '') {
                        params.set('registration_status', filterStatus);
                    }
                    if (filterQuery !== '') {
                        params.set('registration_q', filterQuery);
                    }
                    if (filterGroup !== '') {
                        params.set('registration_group', filterGroup);
                    }
                    if (filterSort !== '') {
                        params.set('registration_sort', filterSort);
                    }
                }

                var queryString = params.toString();

                return queryString !== '' ? (baseUrl + '?' + queryString) : baseUrl;
            };

            var collectRegistrationFilterData = function (panel) {
                var filterScope = panel.querySelector('[data-registration-filter-form]');
                var data = new FormData();

                if (!filterScope) {
                    return data;
                }

                Array.prototype.forEach.call(filterScope.querySelectorAll('[name]'), function (field) {
                    if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
                        data.set(field.name, field.value || '');
                    }
                });

                return data;
            };

            var renderRegistrationFeedback = function (panel, message, isError) {
                var target = panel.querySelector('[data-registration-feedback]');
                if (!target) {
                    return;
                }

                target.innerHTML = '';

                if (!message) {
                    return;
                }

                var box = document.createElement('div');
                box.className = isError ? 'error' : 'status';
                box.textContent = message;
                target.appendChild(box);
            };

            var loadEventRegistrations = function (form, panel, requestUrl) {
                if (!form || !panel) {
                    return;
                }

                var shell = panel.querySelector('[data-event-registrations-shell]');
                if (!shell) {
                    return;
                }

                var targetUrl = requestUrl || buildRegistrationUrl(shell);
                if (targetUrl === '' || shell.getAttribute('data-loading') === '1') {
                    return;
                }

                shell.setAttribute('data-loading', '1');
                shell.innerHTML = '<div class="registration-loading">Meldungen werden geladen...</div>';

                fetch(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error('Registrierungen konnten nicht geladen werden.');
                    }

                    return response.text();
                }).then(function (html) {
                    shell.innerHTML = html;
                    shell.setAttribute('data-loaded', '1');
                }).catch(function () {
                    shell.innerHTML = '<div class="error">Die Meldungen konnten gerade nicht geladen werden.</div>';
                }).finally(function () {
                    shell.removeAttribute('data-loading');
                });
            };

            var submitRegistrationUpdate = function (form, panel, payload) {
                var shell = panel.querySelector('[data-event-registrations-shell]');
                if (!shell || !payload || !payload.get('status')) {
                    return;
                }

                var manageUrl = payload.get('manage_url') || '';
                payload.delete('manage_url');

                if (manageUrl === '') {
                    return;
                }

                var csrfToken = document.querySelector('meta[name="csrf-token"]');
                renderRegistrationFeedback(panel, '', false);

                fetch(manageUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken ? (csrfToken.getAttribute('content') || '') : '',
                        'Accept': 'application/json',
                    },
                    body: payload,
                    credentials: 'same-origin',
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error('Statusänderung fehlgeschlagen.');
                    }

                    return response.json();
                }).then(function (data) {
                    renderRegistrationFeedback(panel, data && data.message ? data.message : 'Meldungen wurden aktualisiert.', false);
                    loadEventRegistrations(form, panel, buildRegistrationUrl(shell, collectRegistrationFilterData(panel)));
                }).catch(function () {
                    renderRegistrationFeedback(panel, 'Die Meldungen konnten nicht aktualisiert werden.', true);
                });
            };

            document.querySelectorAll('form[data-event-form]').forEach(function (form) {
                var defaultTab = form.getAttribute('data-default-event-tab') || 'master-data';

                var activateEventTab = function (tabKey) {
                    form.querySelectorAll('.event-modal-tab-link').forEach(function (link) {
                        var isActive = link.getAttribute('data-event-tab') === tabKey;
                        link.classList.toggle('is-active', isActive);
                        link.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        link.setAttribute('tabindex', isActive ? '0' : '-1');
                    });

                    form.querySelectorAll('[data-event-tab-panel]').forEach(function (panel) {
                        var isActive = panel.getAttribute('data-event-tab-panel') === tabKey;
                        panel.classList.toggle('is-active', isActive);
                        panel.hidden = !isActive;

                        if (isActive && tabKey === 'registrations') {
                            loadEventRegistrations(form, panel);
                        }
                    });
                };

                form.querySelectorAll('.event-modal-tab-link').forEach(function (link) {
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                        var tabKey = link.getAttribute('data-event-tab');
                        if (tabKey) {
                            activateEventTab(tabKey);
                        }
                    });

                    link.addEventListener('keydown', function (event) {
                        moveTabFocus(link, '.event-modal-tab-link', event, function (nextLink) {
                            activateEventTab(nextLink.getAttribute('data-event-tab') || '');
                        });
                    });
                });

                form.addEventListener('click', function (event) {
                    var target = event.target;
                    if (!(target instanceof HTMLElement)) {
                        return;
                    }

                    var registrationsPanel = target.closest('[data-event-tab-panel="registrations"]');
                    if (!registrationsPanel) {
                        return;
                    }

                    var registrationShell = registrationsPanel.querySelector('[data-event-registrations-shell]');
                    if (!registrationShell) {
                        return;
                    }

                    if (target.hasAttribute('data-registration-apply')) {
                        var filterData = collectRegistrationFilterData(registrationsPanel);
                        registrationShell.setAttribute('data-filter-status', filterData.get('registration_status') || 'all');
                        registrationShell.setAttribute('data-filter-query', filterData.get('registration_q') || '');
                        registrationShell.setAttribute('data-filter-group', filterData.get('registration_group') || 'club');
                        registrationShell.setAttribute('data-filter-sort', filterData.get('registration_sort') || 'weight_class');
                        loadEventRegistrations(form, registrationsPanel, buildRegistrationUrl(registrationShell, filterData));
                        return;
                    }

                    if (target.hasAttribute('data-registration-reset')) {
                        registrationShell.setAttribute('data-filter-status', 'all');
                        registrationShell.setAttribute('data-filter-query', '');
                        registrationShell.setAttribute('data-filter-group', 'club');
                        registrationShell.setAttribute('data-filter-sort', 'weight_class');
                        renderRegistrationFeedback(registrationsPanel, '', false);
                        loadEventRegistrations(form, registrationsPanel, registrationShell.getAttribute('data-registrations-url') || '');
                        return;
                    }

                    if (target.hasAttribute('data-registration-group-submit')) {
                        var groupStatusField = target.parentElement ? target.parentElement.querySelector('[data-registration-group-status]') : null;
                        var rawIds = (target.getAttribute('data-registration-ids') || '').split(',').map(function (value) {
                            return value.trim();
                        }).filter(function (value) {
                            return value !== '';
                        });

                        if (!groupStatusField || !rawIds.length) {
                            return;
                        }

                        var groupPayload = collectRegistrationFilterData(registrationsPanel);
                        groupPayload.append('manage_url', target.getAttribute('data-manage-url') || '');
                        groupPayload.append('reason', target.getAttribute('data-reason') || 'club_portal_batch_group');
                        groupPayload.append('status', groupStatusField.value || '');
                        rawIds.forEach(function (registrationId) {
                            groupPayload.append('registration_ids[]', registrationId);
                        });
                        submitRegistrationUpdate(form, registrationsPanel, groupPayload);
                        return;
                    }

                    if (target.hasAttribute('data-registration-selection-submit')) {
                        var groupCard = target.closest('.registration-group-card');
                        var selectionStatusField = groupCard ? groupCard.querySelector('[data-registration-selection-status]') : null;
                        var checkedIds = groupCard ? Array.prototype.map.call(
                            groupCard.querySelectorAll('[data-registration-checkbox]:checked'),
                            function (checkbox) { return checkbox.value; }
                        ) : [];

                        if (!selectionStatusField || !checkedIds.length) {
                            renderRegistrationFeedback(registrationsPanel, 'Bitte mindestens eine Meldung auswählen.', true);
                            return;
                        }

                        var selectionPayload = collectRegistrationFilterData(registrationsPanel);
                        selectionPayload.append('manage_url', target.getAttribute('data-manage-url') || '');
                        selectionPayload.append('reason', target.getAttribute('data-reason') || 'club_portal_batch_selection');
                        selectionPayload.append('status', selectionStatusField.value || '');
                        checkedIds.forEach(function (registrationId) {
                            selectionPayload.append('registration_ids[]', registrationId);
                        });
                        submitRegistrationUpdate(form, registrationsPanel, selectionPayload);
                    }
                });

                form.addEventListener('input', function (event) {
                    if (event.target && event.target.classList && event.target.classList.contains('boxing-age-class-toggle')) {
                        applyBoxingAgeClassToggle(event.target);
                    }

                    syncBoxingAgeClassPanels(form);
                    syncChoicePills(form);
                    syncEventSportPanels(form);
                    syncEventSummary(form);
                });

                form.addEventListener('change', function (event) {
                    if (event.target && event.target.classList && event.target.classList.contains('boxing-age-class-toggle')) {
                        applyBoxingAgeClassToggle(event.target);
                    }

                    syncBoxingAgeClassPanels(form);
                    syncChoicePills(form);
                    syncEventSportPanels(form);
                    syncEventSummary(form);
                });

                syncBoxingAgeClassPanels(form);
                syncChoicePills(form);
                syncEventSportPanels(form);
                syncEventSummary(form);
                activateEventTab(defaultTab);
            });

            var setManagedTabState = function (button, isActive, activeBackground, inactiveBackground) {
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                button.setAttribute('tabindex', isActive ? '0' : '-1');
                button.style.background = isActive ? activeBackground : inactiveBackground;
            };

            var activateFighterModuleTab = function (form, target, tabKey) {
                var tabsContainer = form.querySelector('[data-fighter-module-tabs="' + target + '"]');
                if (!tabsContainer) {
                    return;
                }

                var visibleTabs = Array.prototype.filter.call(
                    tabsContainer.querySelectorAll('.fighter-module-tab-btn'),
                    function (button) {
                        return !button.hidden && button.style.display !== 'none';
                    }
                );

                var activeKey = tabKey;
                if (!visibleTabs.some(function (button) { return button.getAttribute('data-fighter-tab') === activeKey; })) {
                    activeKey = visibleTabs[0] ? visibleTabs[0].getAttribute('data-fighter-tab') : '';
                }

                tabsContainer.querySelectorAll('.fighter-module-tab-btn').forEach(function (button) {
                    var isActive = activeKey !== '' && button.getAttribute('data-fighter-tab') === activeKey && !button.hidden && button.style.display !== 'none';
                    setManagedTabState(button, isActive, '#fff', '');
                });

                form.querySelectorAll('[data-fighter-module-panel]').forEach(function (panel) {
                    var panelKey = panel.getAttribute('data-fighter-module-panel') || '';
                    panel.style.display = panelKey === activeKey ? 'block' : 'none';
                    panel.hidden = panelKey !== activeKey;
                });
            };

            var activateBoxingTab = function (scope, tabKey) {
                if (!scope) {
                    return;
                }

                var visibleTabs = Array.prototype.slice.call(scope.querySelectorAll('.fighter-boxing-tab-btn'));
                var activeKey = tabKey;
                if (!visibleTabs.some(function (button) { return button.getAttribute('data-boxing-tab') === activeKey; })) {
                    activeKey = visibleTabs[0] ? visibleTabs[0].getAttribute('data-boxing-tab') : '';
                }

                scope.querySelectorAll('.fighter-boxing-tab-btn').forEach(function (button) {
                    var isActive = activeKey !== '' && button.getAttribute('data-boxing-tab') === activeKey;
                    setManagedTabState(button, isActive, '#fff', '');
                });

                scope.querySelectorAll('[data-boxing-tab-panel]').forEach(function (panel) {
                    var panelKey = panel.getAttribute('data-boxing-tab-panel') || '';
                    panel.style.display = panelKey === activeKey ? 'block' : 'none';
                    panel.hidden = panelKey !== activeKey;
                });
            };

            var moveTabFocus = function (button, selector, event, activate) {
                if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft' && event.key !== 'Home' && event.key !== 'End') {
                    return;
                }

                event.preventDefault();

                var tabList = button.closest('[role="tablist"]');
                var tabs = Array.prototype.filter.call(
                    tabList ? tabList.querySelectorAll(selector) : [],
                    function (tabButton) {
                        return !tabButton.hidden && tabButton.style.display !== 'none';
                    }
                );

                var currentIndex = tabs.indexOf(button);
                if (currentIndex === -1 || tabs.length === 0) {
                    return;
                }

                var nextIndex = currentIndex;
                if (event.key === 'Home') {
                    nextIndex = 0;
                } else if (event.key === 'End') {
                    nextIndex = tabs.length - 1;
                } else if (event.key === 'ArrowRight') {
                    nextIndex = (currentIndex + 1) % tabs.length;
                } else if (event.key === 'ArrowLeft') {
                    nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
                }

                tabs[nextIndex].focus();
                activate(tabs[nextIndex]);
            };

            var syncFighterPanels = function (form, target) {
                if (!form || !target) {
                    return;
                }

                var checkedModules = [];
                form.querySelectorAll('.fighter-module-checkbox[data-fighter-target="' + target + '"]').forEach(function (checkbox) {
                    if (checkbox.checked) {
                        checkedModules.push(checkbox.value);
                    }
                });

                form.querySelectorAll('[data-fighter-module-panel]').forEach(function (panel) {
                    var panelKey = panel.getAttribute('data-fighter-module-panel') || '';
                    var moduleSlug = panelKey.replace(target + '-', '');
                    panel.style.display = checkedModules.indexOf(moduleSlug) >= 0 ? 'block' : 'none';
                });

                var tabsContainer = form.querySelector('[data-fighter-module-tabs="' + target + '"]');
                if (tabsContainer) {
                    tabsContainer.querySelectorAll('.fighter-module-tab-btn').forEach(function (button) {
                        var tabKey = button.getAttribute('data-fighter-tab') || '';
                        var moduleSlug = tabKey.replace(target + '-', '');
                        var isVisible = checkedModules.indexOf(moduleSlug) >= 0;
                        button.hidden = !isVisible;
                        button.style.display = isVisible ? 'inline-flex' : 'none';
                    });

                    var activeButton = tabsContainer.querySelector('.fighter-module-tab-btn[aria-selected="true"]');
                    activateFighterModuleTab(form, target, activeButton ? activeButton.getAttribute('data-fighter-tab') || '' : '');
                    return;
                }

                form.querySelectorAll('[data-fighter-module-panel]').forEach(function (panel) {
                    panel.style.display = 'none';
                    panel.hidden = true;
                });
            };

            document.querySelectorAll('form[data-fighter-form]').forEach(function (form) {
                var target = form.getAttribute('data-fighter-form') || '';

                form.querySelectorAll('.fighter-module-checkbox').forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        syncFighterPanels(form, target);
                    });
                });

                form.querySelectorAll('.fighter-module-tab-btn').forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        var tabKey = button.getAttribute('data-fighter-tab');
                        if (!tabKey) {
                            return;
                        }

                        activateFighterModuleTab(form, target, tabKey);
                    });

                    button.addEventListener('keydown', function (event) {
                        moveTabFocus(button, '.fighter-module-tab-btn', event, function (nextButton) {
                            activateFighterModuleTab(form, target, nextButton.getAttribute('data-fighter-tab') || '');
                        });
                    });
                });

                form.querySelectorAll('.fighter-boxing-tab-btn').forEach(function (button) {
                    button.addEventListener('keydown', function (event) {
                        var scope = button.closest('[data-fighter-module-panel]');
                        moveTabFocus(button, '.fighter-boxing-tab-btn', event, function (nextButton) {
                            activateBoxingTab(scope, nextButton.getAttribute('data-boxing-tab') || '');
                        });
                    });
                });

                form.querySelectorAll('[data-add-boxing-row]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var key = button.getAttribute('data-add-boxing-row');
                        if (!key) {
                            return;
                        }

                        var template = form.querySelector('template[data-boxing-row-template="' + key + '"]');
                        var boxingTabPanel = button.closest('[data-boxing-tab-panel]');
                        var container = boxingTabPanel ? boxingTabPanel.querySelector('[data-boxing-row-container="' + key + '"]') : null;
                        if (!template || !container || !template.content.firstElementChild) {
                            return;
                        }

                        container.appendChild(template.content.firstElementChild.cloneNode(true));
                    });
                });

                form.addEventListener('click', function (event) {
                    var removeButton = event.target.closest('[data-remove-boxing-row]');
                    if (removeButton) {
                        var row = removeButton.closest('[data-boxing-row]');
                        if (row) {
                            row.remove();
                        }
                    }

                    var boxingTabButton = event.target.closest('.fighter-boxing-tab-btn');
                    if (boxingTabButton) {
                        event.preventDefault();
                        var tabKey = boxingTabButton.getAttribute('data-boxing-tab');
                        if (!tabKey) {
                            return;
                        }

                        activateBoxingTab(boxingTabButton.closest('[data-fighter-module-panel]'), tabKey);
                    }
                });

                syncFighterPanels(form, target);
                form.querySelectorAll('[data-fighter-module-panel]').forEach(function (panel) {
                    if (panel.querySelector('.fighter-boxing-tab-btn')) {
                        var activeBoxingButton = panel.querySelector('.fighter-boxing-tab-btn[aria-selected="true"]');
                        activateBoxingTab(panel, activeBoxingButton ? activeBoxingButton.getAttribute('data-boxing-tab') || '' : '');
                    }
                });
            });

            document.querySelectorAll('.boxing-package-select').forEach(function (select) {
                var sync = function () {
                    var target = select.getAttribute('data-boxing-target');
                    if (!target) {
                        return;
                    }

                    var form = select.closest('form');
                    if (!form) {
                        return;
                    }

                    form.querySelectorAll('[data-boxing-package-panel]').forEach(function (panel) {
                        var panelKey = panel.getAttribute('data-boxing-package-panel') || '';
                        panel.style.display = panelKey === (target + '-' + select.value) ? 'grid' : 'none';
                    });
                };

                select.addEventListener('change', sync);
                sync();
            });
        </script>

    </div>

    @include('partials.main-footer')
    @include('partials.app-scripts')
</body>
</html>