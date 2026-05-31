@extends('admin.layout')

@section('title', 'Module | BaseForFight')

@section('content')
    <div class="header">
        <div>
            <h1>Module</h1>
            <div style="color:var(--ink-soft);">Aktiviere oder deaktiviere Fachmodule für die Plattform.</div>
        </div>
    </div>

    @if (session('status'))
        <div class="card" style="margin-bottom:16px; border-color:#7db928; background:#eff7ea;">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid">
        @foreach ($modules as $module)
            <article class="card" style="grid-column: span 4; display:grid; gap:10px;">
                <div>
                    <strong>{{ $module['name'] }}</strong>
                    <div style="margin-top:6px; color:var(--ink-soft);">{{ $module['description'] }}</div>
                    <div style="margin-top:8px; font-size:13px; color:{{ $module['is_active'] ? '#016734' : '#4d6050' }};">
                        Status: {{ $module['is_active'] ? 'Aktiv' : 'Inaktiv' }}
                    </div>
                    @if (($module['slug'] ?? '') === 'ai')
                        <div style="margin-top:4px; font-size:13px; color:{{ $aiConfigured ? '#016734' : '#dd6850' }};">
                            Konfiguration: {{ $aiConfigured ? 'Vollstaendig' : 'Unvollstaendig' }}
                        </div>
                    @endif
                    @if ($module['activated_at'])
                        <div style="font-size:12px; color:var(--ink-soft);">Aktiviert: {{ $module['activated_at']->format('d.m.Y H:i') }}</div>
                    @endif
                </div>
                <form method="post" action="{{ route('admin.modules.toggle', ['module' => $module['slug']]) }}">
                    @csrf
                    <button class="logout" type="submit" style="background:{{ $module['is_active'] ? '#dd6850' : '#016734' }}; width:100%;">
                        {{ $module['is_active'] ? 'Deaktivieren' : 'Aktivieren' }}
                    </button>
                </form>

                @if (($module['slug'] ?? '') === 'ai')
                    <form method="post" action="{{ route('admin.modules.ai.settings.update') }}" style="display:grid; gap:8px; margin-top:8px;">
                        @csrf
                        @php
                            $selectedProvider = old('provider', (string) ($aiSettings['provider'] ?? 'openai'));
                        @endphp
                        <strong style="font-size:0.95rem;">KI-Regelwerk & Credentials</strong>
                        <label style="font-size:13px; color:var(--ink-soft);">Provider</label>
                        <select name="provider" id="ai-provider-select" required>
                            @foreach ($aiProviderPresets as $providerKey => $preset)
                                <option value="{{ $providerKey }}" @selected($selectedProvider === $providerKey)>{{ $preset['label'] }}</option>
                            @endforeach
                        </select>

                        <label style="font-size:13px; color:var(--ink-soft);">Modell</label>
                        <select name="model" id="ai-model-select" required>
                            @foreach ($aiProviderPresets as $providerKey => $preset)
                                @foreach ((array) ($preset['models'] ?? []) as $modelName)
                                    <option value="{{ $modelName }}" data-provider-model="{{ $providerKey }}" @selected(old('model', (string) ($aiSettings['model'] ?? '')) === $modelName)>{{ $modelName }}</option>
                                @endforeach
                            @endforeach
                        </select>

                        <label style="font-size:13px; color:var(--ink-soft);">Base URL (optional)</label>
                        <input name="base_url" id="ai-base-url-input" placeholder="leer = automatisch, z. B. https://api.openai.com/v1" value="{{ old('base_url', (string) ($aiSettings['base_url'] ?? '')) }}">
                        <div id="ai-base-url-hint" style="margin-top:-2px; font-size:12px; color:var(--ink-soft);"></div>
                        <input type="password" name="api_key" placeholder="{{ $aiHasApiKey ? 'API-Key gesetzt (leer lassen, um beizubehalten)' : 'API-Key' }}">

                        <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:8px;">
                            <input type="number" step="0.01" min="0" max="2" name="temperature" placeholder="Temperature" value="{{ old('temperature', (string) ($aiSettings['parameters']['temperature'] ?? 0.2)) }}">
                            <input type="number" step="0.01" min="0" max="1" name="top_p" placeholder="Top P" value="{{ old('top_p', (string) ($aiSettings['parameters']['top_p'] ?? 1.0)) }}">
                            <input type="number" step="1" min="1" max="65535" name="max_tokens" placeholder="Max Tokens" value="{{ old('max_tokens', (string) ($aiSettings['parameters']['max_tokens'] ?? 4096)) }}">
                        </div>

                        <label style="font-size:13px; color:var(--ink-soft);">Prompt: Eventdaten aus PDF extrahieren</label>
                        <textarea name="prompt_event_extraction" rows="5" required>{{ old('prompt_event_extraction', (string) ($aiSettings['prompts']['event_extraction'] ?? '')) }}</textarea>

                        <label style="font-size:13px; color:var(--ink-soft);">Prompt: Paarungsvorschlaege aus Meldungen</label>
                        <textarea name="prompt_pairing_suggestions" rows="5" required>{{ old('prompt_pairing_suggestions', (string) ($aiSettings['prompts']['pairing_suggestions'] ?? '')) }}</textarea>

                        <button class="logout" type="submit" style="background:#016734; width:100%;">KI-Einstellungen speichern</button>
                    </form>

                    <script>
                        (function () {
                            var providerPresets = @json($aiProviderPresets);
                            var providerSelect = document.getElementById('ai-provider-select');
                            var modelSelect = document.getElementById('ai-model-select');
                            var baseUrlInput = document.getElementById('ai-base-url-input');
                            var baseUrlHint = document.getElementById('ai-base-url-hint');

                            if (!providerSelect || !modelSelect || !baseUrlInput || !baseUrlHint) {
                                return;
                            }

                            var syncModelOptions = function () {
                                var provider = providerSelect.value;
                                var firstVisibleOption = null;

                                Array.prototype.forEach.call(modelSelect.options, function (option) {
                                    var optionProvider = option.getAttribute('data-provider-model') || '';
                                    var shouldShow = optionProvider === provider;
                                    option.hidden = !shouldShow;
                                    if (shouldShow && firstVisibleOption === null) {
                                        firstVisibleOption = option;
                                    }
                                });

                                var selectedOption = modelSelect.options[modelSelect.selectedIndex] || null;
                                if (!selectedOption || selectedOption.hidden) {
                                    if (firstVisibleOption) {
                                        modelSelect.value = firstVisibleOption.value;
                                    }
                                }

                                if (baseUrlInput.value.trim() === '') {
                                    var preset = providerPresets[provider] || null;
                                    if (preset && typeof preset.default_base_url === 'string') {
                                        baseUrlInput.value = preset.default_base_url;
                                    }
                                }

                                if (provider === 'google') {
                                    baseUrlHint.textContent = 'Empfehlung für Google: Base URL leer lassen. Die API-Version wird dann passend zum Modell automatisch versucht.';
                                } else if (provider === 'copilot') {
                                    baseUrlHint.textContent = 'Für Copilot ist eine OpenAI-kompatible Base URL erforderlich.';
                                } else {
                                    baseUrlHint.textContent = 'Leer nutzt die Standard-URL des Providers.';
                                }
                            };

                            providerSelect.addEventListener('change', syncModelOptions);
                            syncModelOptions();
                        })();
                    </script>
                @endif
            </article>
        @endforeach
    </section>
@endsection
