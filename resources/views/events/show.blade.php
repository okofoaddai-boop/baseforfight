<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->title }} | {{ config('brand.name') }}</title>
    <style>
        :root {
            --bg: #f4f6f2;
            --panel: #fafcf8;
            --panel-soft: #f0f6ec;
            --ink: #2d3a2e;
            --ink-soft: #4d6050;
            --line: #c8d4c2;
            --green: #016734;
            --lime: #7db928;
            --danger: #dd6850;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background:
                radial-gradient(circle at 10% 8%, rgba(125, 185, 40, 0.16), transparent 30%),
                radial-gradient(circle at 88% 20%, rgba(1, 103, 52, 0.1), transparent 28%),
                var(--bg);
            color: var(--ink);
            font-family: "Space Grotesk", "Avenir Next", "Segoe UI", sans-serif;
        }

        .wrap {
            width: min(1100px, calc(100% - 24px));
            margin: 18px auto 24px;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 20px;
        }

        .top {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 14px;
            align-items: start;
        }

        .calendar-sheet {
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            background: #fff;
            text-align: center;
        }

        .calendar-sheet .month {
            background: var(--green);
            color: #fff;
            font-weight: 700;
            padding: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 12px;
        }

        .calendar-sheet .day {
            font-size: 2rem;
            line-height: 1;
            font-weight: 800;
            padding: 10px 8px 6px;
            color: var(--ink);
        }

        .calendar-sheet .time {
            font-size: 0.85rem;
            color: var(--ink-soft);
            padding: 0 8px 10px;
            font-weight: 700;
        }

        h1 { margin: 4px 0 8px; font-size: clamp(1.5rem, 3vw, 2rem); }

        .meta-line {
            color: var(--ink-soft);
            margin-top: 4px;
        }

        .status {
            margin-top: 12px;
            padding: 10px 12px;
            border: 1px solid #7db928;
            background: #eef7e9;
            border-radius: 10px;
        }

        .error {
            margin-top: 12px;
            padding: 10px 12px;
            border: 1px solid #e2a29a;
            background: #fff0ed;
            color: #7d2c1f;
            border-radius: 10px;
        }

        .error ul { margin: 0; padding-left: 18px; }

        .badge {
            display: inline-block;
            margin-top: 8px;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            background: #e9f3e4;
            color: var(--green);
        }

        .docs { margin-top: 14px; display: grid; gap: 8px; }
        .docs a { color: var(--green); text-decoration: none; font-weight: 700; }

        .doc-preview {
            display: grid;
            gap: 8px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            padding: 10px;
        }

        .doc-preview iframe {
            width: 100%;
            min-height: 520px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
        }

        .tabs {
            margin-top: 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 6px;
        }

        .tab-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: var(--ink-soft);
            border: 1px solid var(--line);
            border-bottom: 0;
            border-radius: 10px 10px 0 0;
            padding: 9px 14px 10px;
            font-weight: 700;
            background: #f0f4ec;
            margin-bottom: -1px;
        }

        .tab-link.active {
            color: #fff;
            border-color: var(--green);
            background: var(--green);
        }

        .tab-panel {
            margin-top: 0;
            border: 1px solid var(--line);
            border-top: 0;
            border-radius: 0 0 14px 14px;
            background: #fff;
            padding: 14px;
        }

        .class-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .class-card {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px;
            background: var(--panel-soft);
        }

        .class-card h3 {
            margin: 0 0 8px;
            font-size: 0.98rem;
        }

        .class-list {
            margin: 0;
            padding-left: 16px;
            color: var(--ink-soft);
        }

        .age-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .age-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 6px 10px;
            background: #fff;
            color: var(--ink);
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .age-chip.active {
            background: var(--green);
            color: #fff;
            border-color: var(--green);
        }

        .registration-panel {
            margin-top: 18px;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
            background: #fff;
        }

        .split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 10px;
        }

        .col {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--panel-soft);
            padding: 10px;
            min-height: 240px;
        }

        .col h3 {
            margin: 0 0 8px;
            font-size: 1rem;
        }

        .fighter-item {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            padding: 10px;
            display: grid;
            gap: 6px;
        }

        .fighter-item + .fighter-item { margin-top: 8px; }

        .fighter-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
        }

        .fighter-meta {
            color: var(--ink-soft);
            font-size: 0.9rem;
        }

        .fighter-class-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
        }

        .fighter-class {
            border: 1px dashed var(--line);
            border-radius: 8px;
            padding: 6px;
            font-size: 0.82rem;
        }

        .fighter-class strong {
            display: block;
            color: var(--ink-soft);
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
        }

        .btn {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 7px 12px;
            background: #fff;
            color: var(--ink);
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: var(--green);
            color: #fff;
            border-color: var(--green);
        }

        .btn-switch {
            background: #e9f3e4;
            border-color: #a6c7a1;
            color: var(--green);
        }

        .controls {
            margin-top: 12px;
            display: flex;
            justify-content: flex-end;
        }

        @media (max-width: 900px) {
            .top { grid-template-columns: 1fr; }
            .split { grid-template-columns: 1fr; }
            .fighter-class-grid { grid-template-columns: 1fr; }
            .class-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        @if (session()->has('impersonator_id'))
            <section style="margin-bottom:10px; padding:10px 12px; border:1px solid var(--line); border-radius:10px; background:#eef7e9; display:flex; justify-content:space-between; gap:10px; align-items:center;">
                <strong style="font-size:0.92rem;">Support-Simulation aktiv</strong>
                <form method="post" action="{{ route('admin.impersonation.stop') }}">
                    @csrf
                    <button class="btn" type="submit">Zurueck zum Superadmin-Dashboard</button>
                </form>
            </section>
        @endif

        <a href="{{ route('welcome') }}" style="color:var(--green); text-decoration:none; font-weight:700;">Zurueck zur Uebersicht</a>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="top" style="margin-top:10px;">
            <div class="calendar-sheet" aria-label="Kalenderdatum">
                <div class="month">{{ $event->starts_at->locale('de')->translatedFormat('M') }}</div>
                <div class="day">{{ $event->starts_at->format('d') }}</div>
                <div class="time">{{ $event->starts_at->format('H:i') }} Uhr</div>
            </div>
            <div>
                <h1>{{ $event->title }}</h1>
                <div class="meta-line">Los geht's am {{ $event->starts_at->locale('de')->translatedFormat('l, d.m.Y \u\m H:i') }}.</div>
                @if ($event->registration_deadline)
                    <div class="meta-line">Meldeschluss: {{ $event->registration_deadline->locale('de')->translatedFormat('d.m.Y \u\m H:i') }}</div>
                @endif
                @if (! empty($event->location) || ! empty($event->venue_name))
                    <div class="meta-line">{{ $event->location ?: 'Ort folgt' }} @if($event->venue_name) · {{ $event->venue_name }} @endif</div>
                @endif
                @if (! empty($event->entry_fee_cents))
                    <div class="meta-line">Startgebuehr: {{ number_format($event->entry_fee_cents / 100, 2, ',', '.') }} {{ $event->currency ?? 'EUR' }}</div>
                @endif
                @if (! empty($event->boxing_package_key))
                    <div class="meta-line">Regelpaket: {{ $boxingPackageKey ?: $event->boxing_package_key }}</div>
                @endif
                @if (! empty($displayStatus))
                    <span class="badge">{{ strtoupper($displayStatus) }}</span>
                @endif
            </div>
        </section>

        @php
            $activeTab = request()->query('tab', 'details');
            if (! in_array($activeTab, ['details', 'registrations', 'classes'], true)) {
                $activeTab = 'details';
            }

            $docs = is_array($event->info_documents) ? $event->info_documents : [];
            $ageClasses = (array) (($boxingPackage ?? [])['age_classes'] ?? []);
            $performanceClasses = (array) (($boxingPackage ?? [])['performance_classes'] ?? []);
            $allowedAgeCodes = array_values(array_filter((array) ($event->boxing_age_classes ?? []), fn ($v) => is_string($v) && $v !== ''));
            $allowedPerformanceCodes = array_values(array_filter((array) ($event->boxing_performance_classes ?? []), fn ($v) => is_string($v) && $v !== ''));

            $visibleAgeClasses = collect($ageClasses)
                ->map(function ($class, $code) {
                    if (! is_array($class)) {
                        return null;
                    }

                    return [
                        'code' => (string) $code,
                        'name' => (string) ($class['name'] ?? $code),
                        'alter' => is_numeric($class['alter'] ?? null) ? (int) $class['alter'] : null,
                        'weights' => (array) ($class['gewicht'] ?? []),
                    ];
                })
                ->filter(fn ($class) => is_array($class))
                ->when(count($allowedAgeCodes) > 0, fn ($collection) => $collection->whereIn('code', $allowedAgeCodes))
                ->values();

            $visiblePerformanceClasses = collect($performanceClasses)
                ->filter(fn ($class) => is_array($class))
                ->when(count($allowedPerformanceCodes) > 0, fn ($collection) => $collection->filter(function ($class) use ($allowedPerformanceCodes) {
                    $key = (string) ($class['key'] ?? '');

                    return in_array($key, $allowedPerformanceCodes, true);
                }))
                ->map(function ($class) {
                    $label = (string) ($class['name'] ?? ($class['key'] ?? '-'));
                    $winsMin = $class['wins_min'] ?? null;
                    $winsMax = $class['wins_max'] ?? null;

                    return [
                        'key' => (string) ($class['key'] ?? ''),
                        'label' => $label,
                        'range' => (is_numeric($winsMin) ? (string) ((int) $winsMin) : '0') . ' - ' . (is_numeric($winsMax) ? (string) ((int) $winsMax) : 'offen') . ' Siege',
                    ];
                })
                ->values();

            $weightClassMap = [];
            foreach ($visibleAgeClasses as $class) {
                foreach ((array) ($class['weights'] ?? []) as $weightCode => $weightRow) {
                    if (! is_array($weightRow)) {
                        continue;
                    }

                    $label = trim((string) ($weightRow['name'] ?? ''));
                    $short = trim((string) ($weightRow['short'] ?? (string) $weightCode));
                    $mapKey = (string) $weightCode . ':' . $label . ':' . $short;
                    $weightClassMap[$mapKey] = [
                        'short' => $short,
                        'name' => $label !== '' ? $label : (string) $weightCode,
                    ];
                }
            }
            $visibleWeightClasses = collect($weightClassMap)
                ->sortBy('short', SORT_NATURAL)
                ->values();
        @endphp

        <nav class="tabs" role="tablist" aria-label="Veranstaltungsreiter">
            <a id="event-tab-details" role="tab" aria-selected="{{ $activeTab === 'details' ? 'true' : 'false' }}" aria-controls="event-panel-details" tabindex="{{ $activeTab === 'details' ? '0' : '-1' }}" class="tab-link {{ $activeTab === 'details' ? 'active' : '' }}" href="{{ route('events.show', ['event' => $event, 'tab' => 'details']) }}">Details</a>
            <a id="event-tab-registrations" role="tab" aria-selected="{{ $activeTab === 'registrations' ? 'true' : 'false' }}" aria-controls="event-panel-registrations" tabindex="{{ $activeTab === 'registrations' ? '0' : '-1' }}" class="tab-link {{ $activeTab === 'registrations' ? 'active' : '' }}" href="{{ route('events.show', ['event' => $event, 'tab' => 'registrations']) }}">Meldungen</a>
            <a id="event-tab-classes" role="tab" aria-selected="{{ $activeTab === 'classes' ? 'true' : 'false' }}" aria-controls="event-panel-classes" tabindex="{{ $activeTab === 'classes' ? '0' : '-1' }}" class="tab-link {{ $activeTab === 'classes' ? 'active' : '' }}" href="{{ route('events.show', ['event' => $event, 'tab' => 'classes']) }}">Klassenuebersicht</a>
        </nav>

        @if ($activeTab === 'details')
            <section id="event-panel-details" class="tab-panel" role="tabpanel" aria-labelledby="event-tab-details">
                @if (! empty($event->description))
                    <p style="margin:0; line-height:1.6;">{{ $event->description }}</p>
                @else
                    <div class="meta-line" style="margin-top:0;">Keine ausfuehrliche Beschreibung hinterlegt.</div>
                @endif

                @if (count($docs) > 0)
  
                    <div class="docs" style="margin-top:8px;">
                        @foreach ($docs as $doc)
                            <article class="doc-preview">
                                <strong>Dokument {{ $loop->iteration }}</strong>
                                <iframe
                                    src="{{ route('events.documents.show', ['event' => $event, 'documentIndex' => $loop->index]) }}"
                                    title="Dokument {{ $loop->iteration }}"
                                    loading="lazy"
                                ></iframe>
                                <a href="{{ route('events.documents.show', ['event' => $event, 'documentIndex' => $loop->index]) }}" target="_blank" rel="noopener">In neuem Tab oeffnen</a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if ($activeTab === 'classes')
            <section id="event-panel-classes" class="tab-panel" role="tabpanel" aria-labelledby="event-tab-classes">
                <h2 style="margin:0 0 8px;">Zugelassene Klassen dieser Veranstaltung</h2>
                <div class="meta-line" style="margin-top:0;">Diese Uebersicht basiert auf den im Event ausgewaehlten Alters- und Leistungsklassen sowie dem aktiven Regelpaket.</div>

                @php
                    $sortedAgeClasses = $visibleAgeClasses
                        ->sortBy(fn ($class) => is_numeric($class['alter'] ?? null) ? (int) $class['alter'] : 999)
                        ->values();

                    $ageRows = [];
                    $lowerBound = 0;
                    foreach ($sortedAgeClasses as $ageClass) {
                        $upperBound = is_numeric($ageClass['alter'] ?? null) ? (int) $ageClass['alter'] : null;
                        $rangeLabel = $upperBound !== null
                            ? $lowerBound . '-' . $upperBound . ' Jahre'
                            : 'ab ' . $lowerBound . ' Jahre';

                        $ageRows[] = [
                            'code' => (string) $ageClass['code'],
                            'name' => (string) $ageClass['name'],
                            'range' => $rangeLabel,
                            'weights' => collect((array) ($ageClass['weights'] ?? []))
                                ->map(function ($weight, $limit) {
                                    if (! is_array($weight)) {
                                        return null;
                                    }

                                    return [
                                        'limit' => is_numeric($limit) ? (float) $limit : null,
                                        'short' => (string) ($weight['short'] ?? $limit),
                                        'name' => (string) ($weight['name'] ?? $limit),
                                    ];
                                })
                                ->filter(fn ($row) => is_array($row) && $row['limit'] !== null)
                                ->sortBy('limit')
                                ->values()
                                ->all(),
                        ];

                        if ($upperBound !== null) {
                            $lowerBound = $upperBound + 1;
                        }
                    }
                @endphp

                <div class="class-grid" style="margin-top:10px;">
                    <article class="class-card">
                        <h3>Leistungsklassen</h3>
                        @if ($visiblePerformanceClasses->isEmpty())
                            <div class="meta-line" style="margin-top:0;">Keine Leistungsklassen eingeschraenkt.</div>
                        @else
                            <ul class="class-list">
                                @foreach ($visiblePerformanceClasses as $class)
                                    <li>{{ $class['label'] }} ({{ $class['range'] }})</li>
                                @endforeach
                            </ul>
                        @endif
                    </article>

                    <article class="class-card">
                        <h3>Altersklassen (echtes Alter)</h3>
                        @if (count($ageRows) === 0)
                            <div class="meta-line" style="margin-top:0;">Keine Altersklassen eingeschraenkt.</div>
                        @else
                            <div class="age-selector" id="age-class-selector" role="tablist" aria-label="Altersklassen">
                                @foreach ($ageRows as $index => $ageRow)
                                    <a
                                        href="#weight-classes-card"
                                        id="age-tab-{{ $ageRow['code'] }}"
                                        role="tab"
                                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                        aria-controls="weight-classes-card"
                                        tabindex="{{ $index === 0 ? '0' : '-1' }}"
                                        class="age-chip {{ $index === 0 ? 'active' : '' }}"
                                        data-age-chip
                                        data-age-code="{{ $ageRow['code'] }}"
                                        data-age-name="{{ $ageRow['name'] }}"
                                        data-age-range="{{ $ageRow['range'] }}"
                                        data-age-weights='@json($ageRow['weights'])'
                                    >
                                        {{ $ageRow['code'] }} - {{ $ageRow['name'] }} ({{ $ageRow['range'] }})
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>

                    <article class="class-card" id="weight-classes-card" role="tabpanel" aria-labelledby="{{ count($ageRows) > 0 ? 'age-tab-' . $ageRows[0]['code'] : '' }}">
                        <h3>Gewichtsklassen</h3>
                        @if (count($ageRows) === 0)
                            <div class="meta-line" style="margin-top:0;">Keine Gewichtsklassen verfuegbar.</div>
                        @else
                            <div class="meta-line" id="selected-age-label" style="margin-top:0;"></div>
                            <ul class="class-list" id="weight-classes-list" style="margin-top:6px;"></ul>
                        @endif
                    </article>
                </div>
            </section>
        @endif

        @if ($activeTab === 'registrations')
        @auth
            <section id="event-panel-registrations" class="registration-panel" role="tabpanel" aria-labelledby="event-tab-registrations">
                <h2 style="margin:0;">Meldung deiner Kaempfer</h2>
                @if ($isRegistrationOpen)
                    <div class="meta-line" style="margin-top:4px;">Links schon gemeldet, rechts noch moeglich. Ein Klick verschiebt den Kaempfer, unten dann einmal bestaetigen.</div>
                @else
                    <div class="meta-line" style="margin-top:4px;">Meldeschluss ist rum, deswegen zeigen wir dir nur die final gemeldeten Kaempfer.</div>
                @endif

                @if ($isRegistrationOpen)
                    <form method="post" action="{{ route('events.registrations.sync', $event) }}" id="registration-sync-form">
                        @csrf
                        <div class="split">
                            <div class="col">
                                <h3>Gemeldet</h3>
                                <div id="registered-list">
                                    @foreach (($registeredFighters ?? collect()) as $fighter)
                                        @php
                                            $fighterId = (int) $fighter->getKey();
                                            $snapshot = (array) ($fighterSnapshots[$fighterId] ?? []);
                                            $classes = (array) ($snapshot['classes'] ?? []);
                                            $record = (array) ($snapshot['record'] ?? []);
                                        @endphp
                                        <article class="fighter-item" data-fighter-id="{{ $fighterId }}" data-is-registered="1">
                                            <input type="hidden" name="fighter_ids[]" value="{{ $fighterId }}">
                                            <div class="fighter-row">
                                                <strong>{{ $fighter->first_name }} {{ $fighter->last_name }}</strong>
                                                <button class="btn btn-switch" type="button" data-switch-fighter>Raus</button>
                                            </div>
                                            <div class="fighter-meta">Bilanz G/S/N/U: {{ $record['total'] ?? 0 }}/{{ $record['wins'] ?? 0 }}/{{ $record['losses'] ?? 0 }}/{{ $record['draws'] ?? 0 }}</div>
                                            <div class="fighter-meta">Gewicht: {{ $snapshot['weight']['weight_kg'] ?? '-' }} kg</div>
                                            <div class="fighter-class-grid">
                                                <div class="fighter-class"><strong>Altersklasse</strong>{{ $classes['age'] ?? '-' }}</div>
                                                <div class="fighter-class"><strong>Leistung</strong>{{ $classes['performance'] ?? '-' }}</div>
                                                <div class="fighter-class"><strong>Gewicht</strong>{{ $classes['weight'] ?? '-' }}</div>
                                            </div>
                                            <div class="fighter-row">
                                                <span class="fighter-meta">{{ $fighter->club?->name ?? '-' }}</span>
                                                <a class="btn" href="{{ route('clubs.show', ['club' => $fighter->club?->slug, 'tab' => 'fighters', 'edit_fighter' => $fighterId, 'return_event' => $event->getKey()]) }}">Bearbeiten</a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col">
                                <h3>Noch moeglich</h3>
                                <div id="possible-list">
                                    @foreach (($possibleFighters ?? collect()) as $fighter)
                                        @php
                                            $fighterId = (int) $fighter->getKey();
                                            $snapshot = (array) ($fighterSnapshots[$fighterId] ?? []);
                                            $classes = (array) ($snapshot['classes'] ?? []);
                                            $record = (array) ($snapshot['record'] ?? []);
                                        @endphp
                                        <article class="fighter-item" data-fighter-id="{{ $fighterId }}" data-is-registered="0">
                                            <input type="hidden" name="fighter_ids[]" value="{{ $fighterId }}" disabled>
                                            <div class="fighter-row">
                                                <strong>{{ $fighter->first_name }} {{ $fighter->last_name }}</strong>
                                                <button class="btn btn-switch" type="button" data-switch-fighter>Rein</button>
                                            </div>
                                            <div class="fighter-meta">Bilanz G/S/N/U: {{ $record['total'] ?? 0 }}/{{ $record['wins'] ?? 0 }}/{{ $record['losses'] ?? 0 }}/{{ $record['draws'] ?? 0 }}</div>
                                            <div class="fighter-meta">Gewicht: {{ $snapshot['weight']['weight_kg'] ?? '-' }} kg</div>
                                            <div class="fighter-class-grid">
                                                <div class="fighter-class"><strong>Altersklasse</strong>{{ $classes['age'] ?? '-' }}</div>
                                                <div class="fighter-class"><strong>Leistung</strong>{{ $classes['performance'] ?? '-' }}</div>
                                                <div class="fighter-class"><strong>Gewicht</strong>{{ $classes['weight'] ?? '-' }}</div>
                                            </div>
                                            <div class="fighter-row">
                                                <span class="fighter-meta">{{ $fighter->club?->name ?? '-' }}</span>
                                                <a class="btn" href="{{ route('clubs.show', ['club' => $fighter->club?->slug, 'tab' => 'fighters', 'edit_fighter' => $fighterId, 'return_event' => $event->getKey()]) }}">Bearbeiten</a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="controls">
                            <button class="btn btn-primary" type="submit">Bestaetigen</button>
                        </div>
                    </form>
                @else
                    <div class="col" style="margin-top:10px; min-height:0;">
                        <h3>Final gemeldet</h3>
                        @forelse (($registeredFighters ?? collect()) as $fighter)
                            @php
                                $fighterId = (int) $fighter->getKey();
                                $snapshot = (array) ($fighterSnapshots[$fighterId] ?? []);
                                $classes = (array) ($snapshot['classes'] ?? []);
                                $record = (array) ($snapshot['record'] ?? []);
                            @endphp
                            <article class="fighter-item">
                                <strong>{{ $fighter->first_name }} {{ $fighter->last_name }}</strong>
                                <div class="fighter-meta">Bilanz G/S/N/U: {{ $record['total'] ?? 0 }}/{{ $record['wins'] ?? 0 }}/{{ $record['losses'] ?? 0 }}/{{ $record['draws'] ?? 0 }}</div>
                                <div class="fighter-meta">Gewicht: {{ $snapshot['weight']['weight_kg'] ?? '-' }} kg</div>
                                <div class="fighter-class-grid">
                                    <div class="fighter-class"><strong>Altersklasse</strong>{{ $classes['age'] ?? '-' }}</div>
                                    <div class="fighter-class"><strong>Leistung</strong>{{ $classes['performance'] ?? '-' }}</div>
                                    <div class="fighter-class"><strong>Gewicht</strong>{{ $classes['weight'] ?? '-' }}</div>
                                </div>
                            </article>
                        @empty
                            <div class="fighter-meta">Keine Meldungen vorhanden.</div>
                        @endforelse
                    </div>
                @endif
            </section>
        @endauth
        @endif
    </div>

    <script>
        var registrationForm = document.getElementById('registration-sync-form');
        if (registrationForm) {
            registrationForm.querySelectorAll('[data-switch-fighter]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var item = button.closest('.fighter-item');
                    if (!item) {
                        return;
                    }

                    var currentlyRegistered = item.getAttribute('data-is-registered') === '1';
                    var targetList = document.getElementById(currentlyRegistered ? 'possible-list' : 'registered-list');
                    var hiddenInput = item.querySelector('input[type="hidden"][name="fighter_ids[]"]');
                    if (!targetList || !hiddenInput) {
                        return;
                    }

                    item.setAttribute('data-is-registered', currentlyRegistered ? '0' : '1');
                    hiddenInput.disabled = currentlyRegistered;
                    button.textContent = currentlyRegistered ? 'Rein' : 'Raus';
                    targetList.appendChild(item);
                });
            });
        }

        var ageChips = document.querySelectorAll('[data-age-chip]');
        var weightList = document.getElementById('weight-classes-list');
        var selectedAgeLabel = document.getElementById('selected-age-label');

        if (ageChips.length > 0 && weightList && selectedAgeLabel) {
            var renderWeights = function (chip) {
                ageChips.forEach(function (item) {
                    item.classList.remove('active');
                    item.setAttribute('aria-selected', 'false');
                    item.setAttribute('tabindex', '-1');
                });
                chip.classList.add('active');
                chip.setAttribute('aria-selected', 'true');
                chip.setAttribute('tabindex', '0');

                var weightPanel = document.getElementById('weight-classes-card');
                if (weightPanel && chip.id) {
                    weightPanel.setAttribute('aria-labelledby', chip.id);
                }

                var ageName = chip.getAttribute('data-age-name') || '';
                var ageCode = chip.getAttribute('data-age-code') || '';
                var ageRange = chip.getAttribute('data-age-range') || '';
                selectedAgeLabel.textContent = 'Ausgewaehlte Altersklasse: ' + ageCode + ' - ' + ageName + ' (' + ageRange + ')';

                var weights = [];
                try {
                    weights = JSON.parse(chip.getAttribute('data-age-weights') || '[]');
                } catch (e) {
                    weights = [];
                }

                weightList.innerHTML = '';
                if (!Array.isArray(weights) || weights.length === 0) {
                    var emptyItem = document.createElement('li');
                    emptyItem.textContent = 'Keine Gewichtsklassen verfuegbar.';
                    weightList.appendChild(emptyItem);
                    return;
                }

                weights.forEach(function (row) {
                    var item = document.createElement('li');
                    item.textContent = (row.short || '-') + ' - ' + (row.name || '-');
                    weightList.appendChild(item);
                });
            };

            ageChips.forEach(function (chip) {
                chip.addEventListener('click', function (event) {
                    event.preventDefault();
                    renderWeights(chip);
                });

                chip.addEventListener('keydown', function (event) {
                    if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft' && event.key !== 'Home' && event.key !== 'End') {
                        return;
                    }

                    event.preventDefault();

                    var chips = Array.prototype.slice.call(ageChips);
                    var currentIndex = chips.indexOf(chip);
                    if (currentIndex === -1 || chips.length === 0) {
                        return;
                    }

                    var nextIndex = currentIndex;
                    if (event.key === 'Home') {
                        nextIndex = 0;
                    } else if (event.key === 'End') {
                        nextIndex = chips.length - 1;
                    } else if (event.key === 'ArrowRight') {
                        nextIndex = (currentIndex + 1) % chips.length;
                    } else if (event.key === 'ArrowLeft') {
                        nextIndex = (currentIndex - 1 + chips.length) % chips.length;
                    }

                    chips[nextIndex].focus();
                    renderWeights(chips[nextIndex]);
                });
            });

            renderWeights(ageChips[0]);
        }
    </script>
</body>
</html>
