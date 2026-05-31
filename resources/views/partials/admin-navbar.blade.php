@php
    $explainedUrl = \Illuminate\Support\Facades\Route::has('pages.explained') ? route('pages.explained') : url('/bff-leicht-erklaert');
    $pricingUrl = \Illuminate\Support\Facades\Route::has('pages.pricing') ? route('pages.pricing') : url('/preise');
@endphp

<nav class="navbar navbar-expand-lg app-navbar sticky-top">
    <div class="app-navbar-shell container-fluid px-3 px-xl-4">
        <a class="navbar-brand d-inline-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
            <img class="brand-logo" src="{{ asset(config('brand.logo')) }}" alt="{{ config('brand.name') }}">
            <span class="badge text-bg-success-subtle border border-success-subtle rounded-pill ms-1">{{ __('Admin') }}</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="{{ __('Menü öffnen') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-3 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pages.explained') ? 'active' : '' }}" href="{{ $explainedUrl }}">
                        <i class="bi bi-info-circle"></i>
                        <span>{{ trans('pages.nav.explained') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pages.pricing') ? 'active' : '' }}" href="{{ $pricingUrl }}">
                        <i class="bi bi-tags"></i>
                        <span>{{ trans('pages.nav.pricing') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}" href="{{ route('admin.modules.index') }}">
                        <i class="bi bi-grid"></i>
                        <span>{{ __('Module') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clubs.*') ? 'active' : '' }}" href="{{ route('admin.clubs.index') }}">
                        <i class="bi bi-shield-check"></i>
                        <span>{{ __('Clubs & Anfragen') }}</span>
                    </a>
                </li>
                @if (in_array('boxing', $activeModules ?? [], true))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.boxing.settings.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-sliders"></i>
                            <span>{{ __('Box-Settings') }}</span>
                        </a>
                        <ul class="dropdown-menu border-0 shadow-sm">
                            <li><a class="dropdown-item" href="{{ route('admin.boxing.settings.index', ['section' => 'age-classes']) }}">{{ __('Altersklassen') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.boxing.settings.index', ['section' => 'weight-classes']) }}">{{ __('Gewichtsklassen') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.boxing.settings.index', ['section' => 'performance-classes']) }}">{{ __('Leistungsklassen') }}</a></li>
                        </ul>
                    </li>
                @endif
            </ul>

            <div class="app-toolbar ms-lg-3">
                @include('partials.locale-switcher')
                <a class="btn btn-outline-success rounded-pill px-3" href="{{ url('/docs/api/openapi.yaml') }}" target="_blank" rel="noopener">
                    <i class="bi bi-file-earmark-code me-1"></i>OpenAPI
                </a>
                <form method="post" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button class="btn btn-success rounded-pill px-3" type="submit">
                        <i class="bi bi-box-arrow-right me-1"></i>{{ __('Logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>