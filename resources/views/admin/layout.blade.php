<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('BaseForFight Admin'))</title>
    @include('partials.app-assets')
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

        .content {
            background: rgba(255, 253, 249, 0.9);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow);
            padding: 26px;
            margin-top: 16px;
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
    </style>
    @stack('head')
</head>
<body class="app-shell">
    @include('partials.admin-navbar')

    <div class="app-page">
        <main class="content">
            @yield('content')
        </main>
    </div>

    @include('partials.main-footer')
    @include('partials.app-scripts')
</body>
</html>
