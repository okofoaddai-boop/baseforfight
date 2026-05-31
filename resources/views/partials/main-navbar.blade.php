@php
    $user = auth()->user();
@endphp

<nav class="navbar navbar-expand-lg app-navbar sticky-top">
    <div class="app-navbar-shell container-fluid px-3 px-xl-4">
        <a class="navbar-brand d-inline-flex align-items-center" href="{{ route('welcome') }}">
            <img class="brand-logo" src="{{ asset(config('brand.logo')) }}" alt="{{ config('brand.name') }}">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="{{ __('Menü öffnen') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-3 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pages.explained') ? 'active' : '' }}" href="{{ route('pages.explained') }}">
                        <i class="bi bi-info-circle"></i>
                        <span>{{ trans('pages.nav.explained') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pages.pricing') ? 'active' : '' }}" href="{{ route('pages.pricing') }}">
                        <i class="bi bi-tags"></i>
                        <span>{{ trans('pages.nav.pricing') }}</span>
                    </a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clubs.membership.*') ? 'active' : '' }}" href="{{ route('clubs.membership.index') }}">
                            <i class="bi bi-people"></i>
                            <span>{{ __('Meine Vereine') }}</span>
                        </a>
                    </li>
                    @if ($user?->isSuperAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i>
                                <span>SuperUser</span>
                            </a>
                        </li>
                    @elseif ($user?->isPlatformAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.clubs.*') ? 'active' : '' }}" href="{{ route('admin.clubs.index') }}">
                                <i class="bi bi-shield-check"></i>
                                <span>{{ __('Clubs & Anfragen') }}</span>
                            </a>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">
                            <i class="bi bi-person-plus"></i>
                            <span>{{ __('Registrieren') }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span>{{ __('Anmelden') }}</span>
                        </a>
                    </li>
                @endauth
            </ul>

            <div class="app-toolbar ms-lg-3">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-success rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>{{ Str::of($user->name)->before(' ') }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person-lines-fill me-2"></i>{{ __('Meine Daten') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button class="dropdown-item" type="submit">
                                        <i class="bi bi-box-arrow-right me-2"></i>{{ __('Logout') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth

                @include('partials.locale-switcher')
            </div>
        </div>
    </div>
</nav>