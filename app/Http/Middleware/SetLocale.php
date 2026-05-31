<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = config('app.available_locales', ['de']);
        $defaultLocale = config('app.locale', 'de');
        $fallbackLocale = config('app.fallback_locale', $defaultLocale);

        $locale = $request->session()->get('locale', $defaultLocale);

        if (! is_string($locale) || ! in_array($locale, $availableLocales, true)) {
            $locale = $fallbackLocale;
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}