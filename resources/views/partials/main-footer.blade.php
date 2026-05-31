<footer class="app-footer mt-auto">
    <div class="app-footer-shell">
        <div class="app-footer-brand">
            <strong>{{ config('brand.name') }}</strong>
            <span>{{ trans('pages.footer.tagline') }}</span>
        </div>
        <nav class="app-footer-links" aria-label="{{ trans('pages.footer.aria') }}">
            <a class="app-footer-link {{ request()->routeIs('pages.privacy') ? 'active' : '' }}" href="{{ route('pages.privacy') }}">{{ trans('pages.nav.privacy') }}</a>
            <a class="app-footer-link {{ request()->routeIs('pages.imprint') ? 'active' : '' }}" href="{{ route('pages.imprint') }}">{{ trans('pages.nav.imprint') }}</a>
        </nav>
        <div class="app-footer-copy">{{ trans('pages.footer.copy', ['year' => now()->year]) }}</div>
    </div>
</footer>