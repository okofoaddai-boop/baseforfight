<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'BaseForFight Admin')</title>
    <style>
        :root {
            --bg: #f4f6f2;
            --bg-alt: #e8ede4;
            --ink: #2d3a2e;
            --ink-soft: #4d6050;
            --panel: #fafcf8;
            --line: #c8d4c2;
            --accent: #016734;
            --accent-2: #7db928;
            --ok: #016734;
            --warn: #e9c46a;
            --danger: #dd6850;
            --shadow: 0 16px 36px rgba(45, 58, 46, 0.10);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Space Grotesk", "Avenir Next", "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 12% 12%, rgba(125, 185, 40, 0.14) 0%, transparent 34%),
                radial-gradient(circle at 85% 20%, rgba(1, 103, 52, 0.10) 0%, transparent 28%),
                linear-gradient(150deg, var(--bg), var(--bg-alt));
            min-height: 100vh;
        }

        .shell {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
            gap: 20px;
            padding: 20px;
        }

        .sidebar {
            background: rgba(255, 253, 249, 0.92);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            position: sticky;
            top: 20px;
            height: calc(100vh - 40px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand img.icon {
            width: 36px;
            height: 36px;
        }

        .brand img.wordmark {
            max-width: 140px;
            height: auto;
        }

        .tag {
            display: inline-block;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent-2);
            font-weight: 700;
        }

        nav a {
            display: block;
            text-decoration: none;
            color: var(--ink);
            padding: 10px 12px;
            border-radius: 12px;
            margin-bottom: 8px;
            border: 1px solid transparent;
            transition: 180ms ease;
        }

        nav a:hover {
            transform: translateX(4px);
            border-color: var(--line);
            background: var(--panel);
        }

        .content {
            background: rgba(255, 253, 249, 0.9);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 26px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .header h1 {
            margin: 0;
            font-size: clamp(1.4rem, 2.4vw, 2.1rem);
        }

        .logout {
            border: 0;
            border-radius: 999px;
            padding: 10px 16px;
            background: var(--ink);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .logout:hover { background: #0a1f30; }

        .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 16px;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
        }

        .inline-illustration {
            float: left;
            width: 78px;
            max-width: 18vw;
            margin: 0 10px 6px 0;
        }

        @media (max-width: 980px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar {
                position: static;
                height: auto;
            }
        }
    </style>
    @stack('head')
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div>
                <div class="brand">
                    <img class="icon" src="{{ asset(config('brand.icon')) }}" alt="Brand icon">
                    <img class="wordmark" src="{{ asset(config('brand.logo')) }}" alt="{{ config('brand.name') }}">
                </div>
                <span class="tag">Admin Panel</span>
            </div>

            <nav>
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.modules.index') }}">Module</a>
                <a href="{{ route('admin.clubs.index') }}">Clubs & Anfragen</a>
                @if (in_array('boxing', $activeModules ?? [], true))
                    <a href="{{ route('admin.boxing.settings.index', ['section' => 'age-classes']) }}">Box-Settings</a>
                    <a href="{{ route('admin.boxing.settings.index', ['section' => 'age-classes']) }}" style="padding-left:22px;">- Altersklassen</a>
                    <a href="{{ route('admin.boxing.settings.index', ['section' => 'weight-classes']) }}" style="padding-left:22px;">- Gewichtsklassen</a>
                    <a href="{{ route('admin.boxing.settings.index', ['section' => 'performance-classes']) }}" style="padding-left:22px;">- Leistungsklassen</a>
                @endif
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ url('/docs/api/README.md') }}" target="_blank" rel="noopener">API Notes</a>
                <a href="{{ url('/docs/api/openapi.yaml') }}" target="_blank" rel="noopener">OpenAPI v1</a>
            </nav>

            <div style="margin-top: auto; font-size: 12px; color: var(--ink-soft);">
                Zugriff auf API und UI aus einer zentralen Steuerflaeche.
            </div>
        </aside>

        <main class="content">
            @yield('content')
        </main>
    </div>
</body>
</html>
