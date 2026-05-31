<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Willkommen') }} | {{ config('brand.name') }}</title>
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
            --warn: #dd6850;
            --shadow: 0 18px 40px rgba(45, 58, 46, 0.12);
        }

        * { box-sizing: border-box; }

        .page {
            width: min(1480px, calc(100% - 24px));
            margin: 0 auto;
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

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .brand-wordmark {
            width: 170px;
            max-width: 45vw;
            height: auto;
        }

        .top-actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .top-right {
            display: grid;
            justify-items: end;
            gap: 8px;
        }

        .hello {
            font-weight: 700;
            color: var(--ink-soft);
            font-size: 0.95rem;
        }

        .top-menu {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .menu-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
            padding: 6px 2px;
            border-bottom: 2px solid transparent;
            transition: 160ms ease;
        }

        .menu-link:hover,
        .menu-link:focus-visible {
            border-bottom-color: var(--accent-soft);
            color: #014825;
            outline: none;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 10px 15px;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
            transition: 160ms ease;
            font-family: inherit;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 10px 18px rgba(1, 103, 52, 0.2);
        }

        .btn-primary:hover { transform: translateY(-1px); }

        .btn-soft {
            background: var(--panel);
            color: var(--ink);
            border-color: var(--line);
        }

        .impersonation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border: 1px solid var(--accent-soft);
            background: #eef7e9;
            border-radius: 14px;
        }

        .hero {
            padding: 24px;
        }

        .hero h1 {
            margin: 8px 0 0;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            line-height: 1.1;
        }

        .eyebrow {
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent-soft);
            font-weight: 700;
        }

        .lead {
            margin: 14px 0 0;
            color: var(--ink-soft);
            line-height: 1.6;
            max-width: 58ch;
        }

        .inline-illustration {
            float: left;
            width: 88px;
            max-width: 24vw;
            margin: 2px 12px 6px 0;
        }

        .hero-actions {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .auth-middle {
            margin-top: 16px;
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 16px;
            align-items: start;
        }

        .layout {
            margin-top: 16px;
        }

        .section {
            padding: 18px;
        }

        .section h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .muted { color: var(--ink-soft); }

        .events-list,
        .club-list {
            margin-top: 12px;
            display: grid;
            gap: 10px;
        }

        .row {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--panel);
            padding: 12px;
        }

        .event-row {
            display: grid;
            grid-template-columns: 78px 1fr;
            gap: 12px;
            align-items: start;
        }

        .event-date-sheet {
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            text-align: center;
        }

        .event-date-sheet .month {
            background: var(--accent);
            color: #fff;
            padding: 5px 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .event-date-sheet .day {
            font-size: 1.5rem;
            line-height: 1;
            font-weight: 800;
            padding: 8px 4px 5px;
        }

        .event-date-sheet .time {
            color: var(--ink-soft);
            font-size: 0.78rem;
            font-weight: 700;
            padding-bottom: 7px;
        }

        .event-meta {
            margin-top: 5px;
            color: var(--ink-soft);
            font-size: 0.9rem;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pill {
            display: inline-block;
            margin-top: 8px;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: #e9f3e4;
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
        }

        .event-head {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 10px;
        }

        .role-box {
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
        }

        .inline-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
        }

        .club-card-link {
            display: block;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--panel);
            padding: 12px;
            text-decoration: none;
            color: var(--ink);
            transition: 160ms ease;
        }

        .club-card-link:hover,
        .club-card-link:focus-visible {
            border-color: var(--accent-soft);
            box-shadow: 0 8px 20px rgba(1, 103, 52, 0.12);
            transform: translateY(-1px);
            outline: none;
        }

        .club-card-link strong {
            color: var(--accent);
        }

        footer {
            text-align: center;
            margin: 18px 0 10px;
            color: var(--ink-soft);
            font-size: 0.92rem;
        }

        @media (max-width: 980px) {
            .auth-middle { grid-template-columns: 1fr; }
            .layout { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .page { width: calc(100% - 18px); }
            .topbar, .hero, .section { padding: 14px; }
            .top-right { justify-items: stretch; }
            .hello { text-align: right; }
            .event-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="app-shell">
    @include('partials.main-navbar')

    <div class="page">
        <div class="shell">
            @auth
                @if ($isImpersonating)
                    <div class="impersonation">
                        <div><strong>{{ __('Ansicht gewechselt.') }}</strong> {{ __('Du siehst aktuell die Perspektive eines anderen Users.') }}</div>
                        <form method="post" action="{{ route('admin.impersonation.stop') }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">{{ __('Zurück zum Superuser') }}</button>
                        </form>
                    </div>
                @endif
            @endauth

            @auth
                <div class="auth-middle">
                    <section class="section panel" aria-label="{{ __('Meine Vereine') }}">
                        <h2>{{ __('Meine Vereine') }}</h2>
                        @if ($userClubs->isEmpty())
                            <div class="muted" style="margin-top:8px;">{{ __('Keine Vereinszuordnung vorhanden.') }}</div>
                        @else
                            <div class="club-list">
                                @foreach ($userClubs as $club)
                                    <a class="club-card-link" href="{{ route('clubs.show', $club) }}">
                                        <strong>{{ $club->name }}</strong>
                                        <div class="muted" style="margin-top:4px;">{{ $club->user_role_label }} · {{ $club->fighters_count }} {{ __('Kämpfer') }}</div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="hero panel" aria-label="{{ __('Willkommenstext') }}">
                        <div>
                            <div class="eyebrow">{{ __('Willkommen') }}</div>
                            <h1>{{ __('Digitale Plattform für Box- und Kampfsport-Events') }}</h1>
                            <div>
                                <p>
                                    <img class="inline-illustration" src="{{ asset('assets/brand/icons/icon_hello.png') }}" alt="{{ __('Freundlicher Boxer grüßend') }}">
                                    <strong>{{ __('Deine digitale Heimat') }}</strong> {{ __('für alle Box- und Kampfsportveranstaltungen.') }}
                                </p>
                                <p>
                                    <strong>{{ __('Unser Ziel ist klar:') }}</strong> {{ __('eine einfache, schnelle und stressfreie Anmeldung zu allen wichtigen Kampfsportevents, von Turnieren bis zu Lehrgängen.') }}
                                </p>
                                <p>{{ __('BaseForFight ist so intuitiv gestaltet, dass jeder Kämpfer sofort loslegen kann und Veranstalter sowie Trainer spürbar Zeit sparen.') }}</p>
                                <p><strong>{{ __('Durch automatisierte Anmeldungen') }}</strong> {{ __('entlasten wir all jene, die ihre Freizeit und Leidenschaft dem Sport widmen. Gleichzeitig bieten wir allen Kampfsportbegeisterten eine Plattform, die weit über die reine Anmeldung hinausgeht.') }}</p>
                                <p>{{ __('Das Internet macht vieles leichter. Wir nutzen diese Möglichkeiten, um Veranstalter, Trainer und Kämpfer besser zu vernetzen, Abläufe zu vereinfachen und mehr Transparenz in den Kampfsport zu bringen.') }}</p>
                                <p><strong>{{ __('Besonders im Kampfsport') }}</strong> {{ __('wo Respekt und Ehrlichkeit zählen, wollen wir eine Community schaffen, die zusammenhält. Darum entwickeln wir BaseForFight staendig weiter, damit du noch mehr Nutzen daraus ziehen kannst.') }}</p>
                                <p><strong>{{ __('Deine Meinung ist uns wichtig:') }}</strong> {{ __('Wenn dir etwas auffällt oder du eine Idee hast, melde dich einfach. Wir finden gemeinsam eine Lösung.') }}</p>
                            </div>
                            <div class="hero-actions">
                                @if (auth()->user()->isSuperAdmin())
                                    <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">{{ __('Zum Dashboard') }}</a>
                                @elseif ($userClubs->isNotEmpty())
                                    <a class="btn btn-primary" href="{{ route('clubs.show', $userClubs->first()) }}">{{ __('Zu deinem Verein') }}</a>
                                @else
                                    <a class="btn btn-primary" href="{{ route('welcome') }}">{{ __('Zur Startseite') }}</a>
                                @endif
                                @if (auth()->user()->isSuperAdmin())
                                    <a class="btn btn-soft" href="{{ url('/api/v1/health') }}" target="_blank" rel="noopener">{{ __('Systemstatus') }}</a>
                                @endif
                            </div>
                        </div>
                    </section>
                </div>
            @else
                <section class="hero panel">
                    <div>
                        <div class="eyebrow">{{ __('Willkommen') }}</div>
                        <h1>{{ __('Digitale Plattform für Box- und Kampfsport-Events') }}</h1>
                            <div>
                                <p>
                                    <img class="inline-illustration" src="{{ asset('assets/brand/icons/icon_hello.png') }}" alt="{{ __('Freundlicher Boxer grüßend') }}">
                                    <strong>{{ __('Deine digitale Heimat') }}</strong> {{ __('für alle Box- und Kampfsportveranstaltungen.') }}
                                </p>
                                <p>
                                    <strong>{{ __('Unser Ziel ist klar:') }}</strong> {{ __('eine einfache, schnelle und stressfreie Anmeldung zu allen wichtigen Kampfsportevents, von Turnieren bis zu Lehrgängen.') }}
                                </p>
                                <p>{{ __('BaseForFight ist so intuitiv gestaltet, dass jeder Kämpfer sofort loslegen kann und Veranstalter sowie Trainer spürbar Zeit sparen.') }}</p>
                                <p><strong>{{ __('Durch automatisierte Anmeldungen') }}</strong> {{ __('entlasten wir all jene, die ihre Freizeit und Leidenschaft dem Sport widmen. Gleichzeitig bieten wir allen Kampfsportbegeisterten eine Plattform, die weit über die reine Anmeldung hinausgeht.') }}</p>
                                <p>{{ __('Das Internet macht vieles leichter. Wir nutzen diese Möglichkeiten, um Veranstalter, Trainer und Kämpfer besser zu vernetzen, Abläufe zu vereinfachen und mehr Transparenz in den Kampfsport zu bringen.') }}</p>
                                <p><strong>{{ __('Besonders im Kampfsport') }}</strong> {{ __('wo Respekt und Ehrlichkeit zählen, wollen wir eine Community schaffen, die zusammenhält. Darum entwickeln wir BaseForFight staendig weiter, damit du noch mehr Nutzen daraus ziehen kannst.') }}</p>
                                <p><strong>{{ __('Deine Meinung ist uns wichtig:') }}</strong> {{ __('Wenn dir etwas auffällt oder du eine Idee hast, melde dich einfach. Wir finden gemeinsam eine Lösung.') }}</p>
                            </div>
                        <div class="hero-actions">
                            <a class="btn btn-primary" href="{{ route('login') }}">{{ __('Jetzt einloggen') }}</a>
                        </div>
                    </div>
                </section>
            @endauth

            <div class="layout">
                <section class="section panel" aria-label="{{ __('Eventkalender Vorschau') }}">
                    <h2>{{ __('Eventkalender') }}</h2>
                    @if ($isPrivilegedView)
                        <div class="muted" style="margin-top:6px;">{{ __('Als Admin siehst du auch Entwürfe, damit du in Ruhe planen kannst.') }}</div>
                    @endif
                    <div class="events-list">
                        @forelse ($events as $event)
                            <article class="row event-row">
                                <div class="event-date-sheet" aria-label="{{ __('Veranstaltungsdatum') }}">
                                    <div class="month">{{ $event->starts_at->locale(app()->getLocale())->translatedFormat('M') }}</div>
                                    <div class="day">{{ $event->starts_at->format('d') }}</div>
                                    <div class="time">{{ $event->starts_at->format('H:i') }}</div>
                                </div>

                                <div>
                                    <div class="event-head">
                                        <strong>{{ $event->title }}</strong>
                                        <a class="inline-link" href="{{ route('events.show', $event) }}">{{ __('Ansehen') }}</a>
                                    </div>
                                    @if (! empty($event->description))
                                        <div class="muted" style="margin-top:4px;">{{ \Illuminate\Support\Str::limit($event->description, 120) }}</div>
                                    @endif
                                    <div class="event-meta">
                                        @if (! empty($event->location))
                                            <span>{{ __('Ort:') }} {{ $event->location }}</span>
                                        @endif
                                        @if (! empty($event->venue_name))
                                            <span>{{ __('Halle:') }} {{ $event->venue_name }}</span>
                                        @endif
                                        @if (! empty($event->entry_fee_cents))
                                            <span>{{ __('Gebuehr:') }} {{ number_format($event->entry_fee_cents / 100, 2, ',', '.') }} {{ $event->currency ?? 'EUR' }}</span>
                                        @endif
                                        @if ($event->registration_deadline)
                                            <span>{{ __('Anmeldeschluss:') }} {{ $event->registration_deadline->format('d.m.Y H:i') }}</span>
                                        @endif
                                        @if (! empty($event->max_registrations))
                                            <span>{{ __('Max:') }} {{ $event->max_registrations }}</span>
                                        @endif
                                        @if ($event->allow_waitlist)
                                            <span>{{ __('Warteliste erlaubt') }}</span>
                                        @endif
                                        @if (! empty($event->boxing_package_key))
                                            <span>{{ __('Box-Paket:') }} {{ $event->boxing_package_key }}</span>
                                        @endif
                                    </div>
                                    @if (! empty($event->display_status))
                                        <span class="pill">{{ $event->display_status }}</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="row">
                                <strong>{{ __('Aktuell keine Veranstaltungen verfügbar.') }}</strong>
                                <div class="muted" style="margin-top:4px;">{{ __('Sobald Events veröffentlicht sind, erscheinen sie hier automatisch.') }}</div>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

        </div>
    </div>

    @include('partials.main-footer')
    @include('partials.app-scripts')
</body>
</html>