<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | BaseForFight</title>
    <style>
        :root {
            --bg: #f4f6f2;
            --panel: #fafcf8;
            --ink: #2d3a2e;
            --ink-soft: #4d6050;
            --line: #c8d4c2;
            --accent: #016734;
            --danger: #dd6850;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Space Grotesk", "Avenir Next", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 20% 20%, rgba(125, 185, 40, 0.15) 0%, transparent 30%),
                radial-gradient(circle at 80% 15%, rgba(1, 103, 52, 0.10) 0%, transparent 32%),
                linear-gradient(120deg, #f4f6f2, #e8ede4);
            color: var(--ink);
            padding: 18px;
        }

        .panel {
            width: min(460px, 100%);
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(19, 41, 61, 0.14);
        }

        h1 { margin: 0 0 6px 0; }
        p { margin-top: 0; color: var(--ink-soft); }

        .inline-illustration {
            float: left;
            width: 72px;
            max-width: 22vw;
            margin: 0 10px 6px 0;
        }

        label {
            display: block;
            font-weight: 700;
            margin: 12px 0 6px;
        }

        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 12px;
            font: inherit;
            background: var(--panel);
        }

        .btn {
            margin-top: 18px;
            width: 100%;
            border: 0;
            border-radius: 999px;
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            padding: 12px;
            cursor: pointer;
        }

        .error {
            margin-top: 10px;
            color: var(--danger);
            font-size: 14px;
        }

        .ok {
            margin-bottom: 12px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid #7db928;
            background: #eef7e9;
            color: var(--ink);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <form class="panel" action="{{ route('login.submit') }}" method="post">
        @csrf
        <h1>Anmelden</h1>
        <p><img class="inline-illustration" src="{{ asset('assets/brand/icons/icon_pass.png') }}" alt="Boxer mit Zugangspass">Ein Login für Trainer, Manager, Admins und SuperUser.</p>

        @if (session('status'))
            <div class="ok">{{ session('status') }}</div>
        @endif

        <label for="email">E-Mail</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Passwort</label>
        <input id="password" type="password" name="password" required>

        <button class="btn" type="submit">Einloggen</button>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
    </form>
</body>
</html>
