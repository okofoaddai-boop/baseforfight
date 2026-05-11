<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrierung | BaseForFight</title>
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
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            color: var(--ink);
            font-family: "Space Grotesk", "Avenir Next", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 15% 10%, rgba(125, 185, 40, 0.16), transparent 30%),
                radial-gradient(circle at 85% 18%, rgba(1, 103, 52, 0.1), transparent 30%),
                linear-gradient(150deg, var(--bg), var(--bg-alt));
        }
        .panel {
            width: min(560px, 100%);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 24px;
        }
        h1 { margin: 0 0 8px; }
        p { margin: 0 0 16px; color: var(--ink-soft); }

        .inline-illustration {
            float: left;
            width: 74px;
            max-width: 22vw;
            margin: 0 10px 6px 0;
        }
        label { display: block; margin: 10px 0 6px; font-weight: 700; }
        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 12px;
            font: inherit;
            background: #fff;
        }
        .btn {
            margin-top: 16px;
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: var(--green);
            color: #fff;
            font-weight: 700;
            padding: 12px;
            cursor: pointer;
        }
        .hint { font-size: 13px; color: var(--ink-soft); margin-top: 8px; }
        .error { color: var(--danger); margin-top: 10px; }
        .ok {
            border: 1px solid var(--green-light);
            background: #eef7e9;
            color: var(--green);
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 12px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <form class="panel" action="{{ route('register.store') }}" method="post">
        @csrf
        <h1>Trainer registrieren</h1>
        <p><img class="inline-illustration" src="{{ asset('assets/brand/icons/icon_sign_howto.png') }}" alt="Boxer zeigt auf Anleitung">Registriere dich mit Verein. Bestehende Vereine erhalten eine Manager-Anfrage, neue Vereine starten mit Manager-Rolle.</p>

        @if (session('status'))
            <div class="ok">{{ session('status') }}</div>
        @endif

        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name') }}" required>

        <label for="email">E-Mail</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="club_name">Verein</label>
        <input id="club_name" name="club_name" value="{{ old('club_name') }}" required>
        <div class="hint">Tipp: Bitte offiziellen Vereinsnamen verwenden, um Duplikate zu vermeiden.</div>

        <label for="password">Passwort</label>
        <input id="password" type="password" name="password" required>

        <label for="password_confirmation">Passwort wiederholen</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button class="btn" type="submit">Konto erstellen</button>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <div class="hint" style="margin-top:12px;">Nach der Registrierung erhaeltst du einen Double-Opt-In-Link per E-Mail.</div>
        <div class="hint"><a href="{{ route('welcome') }}" style="color:var(--green)">Zurueck zur Startseite</a></div>
    </form>
</body>
</html>
