<div class="locale-switcher" aria-label="{{ __('Sprachauswahl') }}">
    @foreach (config('app.available_locales', ['de']) as $locale)
        @php
            $isActiveLocale = app()->getLocale() === $locale;
            $flagClass = match ($locale) {
                'de' => 'fi fi-de',
                'en' => 'fi fi-gb',
                'fr' => 'fi fi-fr',
                default => 'bi bi-translate',
            };
            $label = match ($locale) {
                'de' => 'Deutsch',
                'en' => 'English',
                'fr' => 'Français',
                default => strtoupper($locale),
            };
        @endphp
        <form method="post" action="{{ route('locale.update') }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $locale }}">
            <button type="submit" class="locale-btn{{ $isActiveLocale ? ' active' : '' }}" title="{{ $label }}" aria-label="{{ $label }}">
                @if (str_starts_with($flagClass, 'fi '))
                    <span class="{{ $flagClass }}"></span>
                @else
                    <i class="{{ $flagClass }}"></i>
                @endif
            </button>
        </form>
    @endforeach
</div>