<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Willkommen | {{ config('brand.name') }}</title>
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

        body {
            margin: 0;
            font-family: "Space Grotesk", "Avenir Next", "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 12% 12%, rgba(125, 185, 40, 0.14), transparent 34%),
                radial-gradient(circle at 85% 16%, rgba(1, 103, 52, 0.08), transparent 28%),
                linear-gradient(160deg, var(--bg), var(--bg-alt));
            min-height: 100vh;
        }

        .page {
            width: min(1140px, calc(100% - 28px));
            margin: 16px auto 26px;
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
<body>
    <div class="page">
        <div class="shell">
            <header class="topbar panel">
                <a class="brand" href="{{ route('welcome') }}" aria-label="Startseite">
                    <img class="brand-wordmark" src="{{ asset(config('brand.logo')) }}" alt="{{ config('brand.name') }}">
                </a>
                <div class="top-right">
                    @auth
                        <div class="hello">
                            Hallo {{ Str::of(auth()->user()->name)->before(' ') }}
                            @if (auth()->user()->isSuperAdmin())
                                (Superadmin)
                            @elseif (auth()->user()->isPlatformAdmin())
                                (Admin)
                            @endif
                        </div>
                    @endauth
                    <div class="top-actions">
                        <nav class="top-menu" aria-label="Hauptmenue">
                    @guest
                            <a class="menu-link" href="{{ route('register') }}">Registrieren</a>
                            <a class="menu-link" href="{{ route('login') }}">Anmelden</a>
                    @else
                        @if (auth()->user()->isSuperAdmin())
                                <a class="menu-link" href="{{ route('admin.dashboard') }}">SuperUser</a>
                        @elseif (auth()->user()->isPlatformAdmin())
                                <a class="menu-link" href="{{ route('admin.clubs.index') }}">Clubs & Anfragen</a>
                        @endif
                            <a class="menu-link" href="{{ route('welcome') }}">Startseite</a>
                    @endguest
                        </nav>
                        @auth
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-primary" type="submit">Logout</button>
                            </form>
                        @endauth
                    </div>
                </div>
            </header>

            @auth
                @if ($isImpersonating)
                    <div class="impersonation">
                        <div><strong>Ansicht gewechselt.</strong> Du siehst aktuell die Perspektive eines anderen Users.</div>
                        <form method="post" action="{{ route('admin.impersonation.stop') }}">
                            @csrf
                            <button class="btn btn-primary" type="submit">Zurueck zum Superuser</button>
                        </form>
                    </div>
                @endif
            @endauth

            @auth
                <div class="auth-middle">
                    <section class="section panel" aria-label="Meine Vereine">
                        <h2>Meine Vereine</h2>
                        @if ($userClubs->isEmpty())
                            <div class="muted" style="margin-top:8px;">Keine Vereinszuordnung vorhanden.</div>
                        @else
                            <div class="club-list">
                                @foreach ($userClubs as $club)
                                    <a class="club-card-link" href="{{ route('clubs.show', $club) }}">
                                        <strong>{{ $club->name }}</strong>
                                        <div class="muted" style="margin-top:4px;">{{ strtoupper($club->pivot->role) }} · {{ $club->fighters_count }} Kaempfer</div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="hero panel" aria-label="Willkommenstext">
                        <div>
                            <div class="eyebrow">Willkommen</div>
                            <h1>Digitale Plattform fuer Box- und Kampfsport-Events</h1>
                            <div>
                                <p>
                                    <img class="inline-illustration" src="{{ asset('assets/brand/icons/icon_hello.png') }}" alt="Freundlicher Boxer gruessend">
                                    <strong>Deine digitale Heimat</strong> fuer alle Box- und Kampfsportveranstaltungen.
                                </p>
                                <p>
                                    <strong>Unser Ziel ist klar:</strong> eine einfache, schnelle und stressfreie Anmeldung zu allen wichtigen Kampfsportevents, von Turnieren bis zu Lehrgaengen.
                                </p>
                                <p>
                                    BaseForFight ist so intuitiv gestaltet, dass jeder Kaempfer sofort loslegen kann und Veranstalter sowie Trainer spuerbar Zeit sparen.
                                </p>
                                <p>
                                    <strong>Durch automatisierte Anmeldungen</strong> entlasten wir all jene, die ihre Freizeit und Leidenschaft dem Sport widmen. Gleichzeitig bieten wir allen Kampfsportbegeisterten eine Plattform, die weit ueber die reine Anmeldung hinausgeht.
                                </p>
                                <p>
                                    Das Internet macht vieles leichter. Wir nutzen diese Moeglichkeiten, um Veranstalter, Trainer und Kaempfer besser zu vernetzen, Ablaeufe zu vereinfachen und mehr Transparenz in den Kampfsport zu bringen.
                                </p>
                                <p>
                                    <strong>Besonders im Kampfsport</strong>, wo Respekt und Ehrlichkeit zaehlen, wollen wir eine Community schaffen, die zusammenhaelt. Darum entwickeln wir BaseForFight staendig weiter, damit du noch mehr Nutzen daraus ziehen kannst.
                                </p>
                                <p>
                                    <strong>Deine Meinung ist uns wichtig:</strong> Wenn dir etwas auffaellt oder du eine Idee hast, melde dich einfach. Wir finden gemeinsam eine Loesung.
                                </p>
                            </div>
                            <div class="hero-actions">
                                @if (auth()->user()->isSuperAdmin())
                                    <a class="btn btn-primary" href="{{ route('admin.dashboard') }}">Zum Dashboard</a>
                                @elseif ($userClubs->isNotEmpty())
                                    <a class="btn btn-primary" href="{{ route('clubs.show', $userClubs->first()) }}">Zu deinem Verein</a>
                                @else
                                    <a class="btn btn-primary" href="{{ route('welcome') }}">Zur Startseite</a>
                                @endif
                                @if (auth()->user()->isSuperAdmin())
                                    <a class="btn btn-soft" href="{{ url('/api/v1/health') }}" target="_blank" rel="noopener">Systemstatus</a>
                                @endif
                            </div>
                        </div>
                    </section>
                </div>
            @else
                <section class="hero panel">
                    <div>
                        <div class="eyebrow">Willkommen</div>
                        <h1>Digitale Plattform fuer Box- und Kampfsport-Events</h1>
                            <div>
                                <p>
                                    <img class="inline-illustration" src="{{ asset('assets/brand/icons/icon_hello.png') }}" alt="Freundlicher Boxer gruessend">
                                    <strong>Deine digitale Heimat</strong> fuer alle Box- und Kampfsportveranstaltungen.
                                </p>
                                <p>
                                    <strong>Unser Ziel ist klar:</strong> eine einfache, schnelle und stressfreie Anmeldung zu allen wichtigen Kampfsportevents, von Turnieren bis zu Lehrgaengen.
                                </p>
                                <p>
                                    BaseForFight ist so intuitiv gestaltet, dass jeder Kaempfer sofort loslegen kann und Veranstalter sowie Trainer spuerbar Zeit sparen.
                                </p>
                                <p>
                                    <strong>Durch automatisierte Anmeldungen</strong> entlasten wir all jene, die ihre Freizeit und Leidenschaft dem Sport widmen. Gleichzeitig bieten wir allen Kampfsportbegeisterten eine Plattform, die weit ueber die reine Anmeldung hinausgeht.
                                </p>
                                <p>
                                    Das Internet macht vieles leichter. Wir nutzen diese Moeglichkeiten, um Veranstalter, Trainer und Kaempfer besser zu vernetzen, Ablaeufe zu vereinfachen und mehr Transparenz in den Kampfsport zu bringen.
                                </p>
                                <p>
                                    <strong>Besonders im Kampfsport</strong>, wo Respekt und Ehrlichkeit zaehlen, wollen wir eine Community schaffen, die zusammenhaelt. Darum entwickeln wir BaseForFight staendig weiter, damit du noch mehr Nutzen daraus ziehen kannst.
                                </p>
                                <p>
                                    <strong>Deine Meinung ist uns wichtig:</strong> Wenn dir etwas auffaellt oder du eine Idee hast, melde dich einfach. Wir finden gemeinsam eine Loesung.
                                </p>
                            </div>
                        <div class="hero-actions">
                            <a class="btn btn-primary" href="{{ route('login') }}">Jetzt einloggen</a>
                        </div>
                    </div>
                </section>
            @endauth

            <div class="layout">
                <section class="section panel" aria-label="Eventkalender Vorschau">
                    <h2>Eventkalender</h2>
                    @if ($isPrivilegedView)
                        <div class="muted" style="margin-top:6px;">Als Admin siehst du auch Entwuerfe, damit du in Ruhe planen kannst.</div>
                    @endif
                    <div class="events-list">
                        @forelse ($events as $event)
                            <article class="row event-row">
                                <div class="event-date-sheet" aria-label="Veranstaltungsdatum">
                                    <div class="month">{{ $event->starts_at->locale('de')->translatedFormat('M') }}</div>
                                    <div class="day">{{ $event->starts_at->format('d') }}</div>
                                    <div class="time">{{ $event->starts_at->format('H:i') }}</div>
                                </div>

                                <div>
                                    <div class="event-head">
                                        <strong>{{ $event->title }}</strong>
                                        <a class="inline-link" href="{{ route('events.show', $event) }}">Ansehen</a>
                                    </div>
                                    @if (! empty($event->description))
                                        <div class="muted" style="margin-top:4px;">{{ \Illuminate\Support\Str::limit($event->description, 120) }}</div>
                                    @endif
                                    <div class="event-meta">
                                        @if (! empty($event->location))
                                            <span>Ort: {{ $event->location }}</span>
                                        @endif
                                        @if (! empty($event->venue_name))
                                            <span>Halle: {{ $event->venue_name }}</span>
                                        @endif
                                        @if (! empty($event->entry_fee_cents))
                                            <span>Gebuehr: {{ number_format($event->entry_fee_cents / 100, 2, ',', '.') }} {{ $event->currency ?? 'EUR' }}</span>
                                        @endif
                                        @if ($event->registration_deadline)
                                            <span>Anmeldeschluss: {{ $event->registration_deadline->format('d.m.Y H:i') }}</span>
                                        @endif
                                        @if (! empty($event->max_registrations))
                                            <span>Max: {{ $event->max_registrations }}</span>
                                        @endif
                                        @if ($event->allow_waitlist)
                                            <span>Warteliste erlaubt</span>
                                        @endif
                                        @if (! empty($event->boxing_package_key))
                                            <span>Box-Paket: {{ $event->boxing_package_key }}</span>
                                        @endif
                                    </div>
                                    @if (! empty($event->display_status))
                                        <span class="pill">{{ strtoupper($event->display_status) }}</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="row">
                                <strong>Aktuell keine Veranstaltungen verfuegbar.</strong>
                                <div class="muted" style="margin-top:4px;">Sobald Events veroeffentlicht sind, erscheinen sie hier automatisch.</div>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <footer>
                © {{ now()->year }} baseforfight.de - Friendly Combat Sports Operations
            </footer>
        </div>
    </div>
</body>
</html>