<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page['title'] ?? config('brand.name') }} | {{ config('brand.name') }}</title>
    @include('partials.app-assets')
    <style>
        .content-page {
            width: min(1100px, calc(100% - 24px));
            margin: 0 auto;
            padding: 1rem 0 2rem;
        }

        .content-shell {
            display: grid;
            gap: 16px;
        }

        .content-hero,
        .content-section {
            background: rgba(255, 252, 246, 0.9);
            border: 1px solid var(--bf-line);
            border-radius: 1.4rem;
            box-shadow: var(--bf-shadow);
            padding: 1.5rem;
        }

        .content-kicker {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--bf-accent-soft);
            font-weight: 700;
            margin-bottom: 0.55rem;
        }

        .content-title {
            margin: 0;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            letter-spacing: -0.03em;
        }

        .content-intro {
            margin: 0.85rem 0 0;
            color: var(--bf-ink-soft);
            font-size: 1.05rem;
            max-width: 70ch;
        }

        .content-section h2 {
            margin: 0 0 0.8rem;
            font-size: 1.25rem;
        }

        .content-section p:last-child,
        .content-section ul:last-child {
            margin-bottom: 0;
        }

        .content-section ul {
            margin: 0;
            padding-left: 1.2rem;
        }
    </style>
</head>
<body class="app-shell">
    @include('partials.main-navbar')

    <main class="content-page">
        <div class="content-shell">
            <section class="content-hero">
                <div class="content-kicker">{{ $page['eyebrow'] ?? config('brand.name') }}</div>
                <h1 class="content-title">{{ $page['title'] ?? config('brand.name') }}</h1>
                @if (!empty($page['intro']))
                    <p class="content-intro">{{ $page['intro'] }}</p>
                @endif
            </section>

            @foreach ($sections as $section)
                <section class="content-section">
                    @if (!empty($section['title']))
                        <h2>{{ $section['title'] }}</h2>
                    @endif

                    @foreach ((array) ($section['body'] ?? []) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach

                    @if (!empty($section['items']) && is_array($section['items']))
                        <ul>
                            @foreach ($section['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        </div>
    </main>

    @include('partials.main-footer')
    @include('partials.app-scripts')
</body>
</html>