@extends('admin.layout')

@section('title', 'Box-Settings | BaseForFight')

@section('content')
    <div class="header">
        <div>
            <h1>Box-Settings</h1>
            <div style="color:var(--ink-soft);">Pakete sind global und enthalten Leistungsklassen, Altersklassen und Gewichtsklassen pro Altersklasse.</div>
        </div>
    </div>

    <div class="card" style="margin-bottom:14px;">
        @if (session('status'))
            <div style="margin-bottom:10px; border:1px solid #7db928; background:#eff7ea; border-radius:12px; padding:10px 12px;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="margin-bottom:10px; border:1px solid #e2a29a; background:#fff0ed; border-radius:12px; padding:10px 12px; color:#7d2c1f;">
                {{ $errors->first() }}
            </div>
        @endif

        <div style="display:grid; gap:12px; margin-bottom:14px; border:1px solid var(--line); border-radius:12px; padding:12px; background:#fff;">
            <div style="color:var(--ink-soft);">Aktives Paket: <strong style="color:var(--accent);">{{ $activePackage ?: 'keins' }}</strong></div>
            <form method="post" action="{{ route('admin.boxing.settings.update', ['section' => 'performance-classes']) }}" style="display:grid; grid-template-columns:1fr 1.2fr 1.2fr auto; gap:8px; align-items:end;">
                @csrf
                <input type="hidden" name="_action" value="create-package">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:6px;">Paket-Key</label>
                    <input name="new_package_key" placeholder="z. B. dbv" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:9px;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:6px;">Paketname</label>
                    <input name="new_package_name" placeholder="Name" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:9px;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:6px;">Quelle</label>
                    <input name="new_package_source" placeholder="Regelwerk" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:9px;">
                </div>
                <button class="logout" type="submit" style="background:#016734;">Paket anlegen</button>
            </form>
        </div>

        <div style="display:grid; gap:10px;">
            @forelse ($packages as $packageKey => $package)
                @php
                    $packageName = trim((string) ($package['name'] ?? $packageKey));
                    $ageClasses = (array) ($package['age_classes'] ?? []);
                    $performanceClasses = (array) ($package['performance_classes'] ?? []);
                    $tabId = preg_replace('/[^a-z0-9_-]+/i', '-', $packageKey) ?: 'package';
                @endphp

                <details style="border:1px solid var(--line); border-radius:12px; background:#fff;">
                    <summary style="cursor:pointer; list-style:none; padding:12px 14px; display:flex; justify-content:space-between; align-items:center; gap:10px;">
                        <span>
                            <strong>{{ $packageName }}</strong>
                            <span style="margin-left:8px; color:var(--ink-soft);">({{ $packageKey }})</span>
                            <span style="margin-left:8px; font-size:12px; font-weight:700; color:{{ $activePackage === $packageKey ? '#016734' : '#4d6050' }};">
                                {{ $activePackage === $packageKey ? 'AKTIV' : 'INAKTIV' }}
                            </span>
                        </span>
                        <span style="font-size:12px; color:var(--ink-soft);">aufklappen</span>
                    </summary>

                    <div style="padding:0 14px 14px; display:grid; gap:12px;">
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <form method="post" action="{{ route('admin.boxing.settings.update', ['section' => 'performance-classes']) }}" onclick="event.stopPropagation();">
                                @csrf
                                <input type="hidden" name="_action" value="toggle-package">
                                <input type="hidden" name="package_key" value="{{ $packageKey }}">
                                <button class="logout" type="submit" style="background:{{ $activePackage === $packageKey ? '#2d3a2e' : '#016734' }};">
                                    {{ $activePackage === $packageKey ? 'Deaktivieren' : 'Aktivieren' }}
                                </button>
                            </form>
                            <form method="post" action="{{ route('admin.boxing.settings.update', ['section' => 'performance-classes']) }}" onsubmit="return confirm('Paket wirklich loeschen?');" onclick="event.stopPropagation();">
                                @csrf
                                <input type="hidden" name="_action" value="delete-package">
                                <input type="hidden" name="package_key" value="{{ $packageKey }}">
                                <button class="logout" type="submit" style="background:#dd6850;">Loeschen</button>
                            </form>
                        </div>

                        <div role="tablist" aria-label="Box-Paketbereiche" style="display:flex; gap:8px; flex-wrap:wrap; border-bottom:1px solid var(--line); padding-bottom:8px;">
                            <a href="#perf-{{ $tabId }}" id="perf-{{ $tabId }}-tab" role="tab" aria-selected="true" aria-controls="perf-{{ $tabId }}" tabindex="0" class="pkg-tab-btn active" data-tab-target="perf-{{ $tabId }}" style="border:1px solid var(--line); background:#fff; border-radius:10px; padding:8px 10px; font-weight:700; cursor:pointer; text-decoration:none; color:inherit;">Leistungsklassen</a>
                            <a href="#age-{{ $tabId }}" id="age-{{ $tabId }}-tab" role="tab" aria-selected="false" aria-controls="age-{{ $tabId }}" tabindex="-1" class="pkg-tab-btn" data-tab-target="age-{{ $tabId }}" style="border:1px solid var(--line); background:#f6f8f7; border-radius:10px; padding:8px 10px; font-weight:700; cursor:pointer; text-decoration:none; color:inherit;">Altersklassen</a>
                            <a href="#pass-{{ $tabId }}" id="pass-{{ $tabId }}-tab" role="tab" aria-selected="false" aria-controls="pass-{{ $tabId }}" tabindex="-1" class="pkg-tab-btn" data-tab-target="pass-{{ $tabId }}" style="border:1px solid var(--line); background:#f6f8f7; border-radius:10px; padding:8px 10px; font-weight:700; cursor:pointer; text-decoration:none; color:inherit;">Kampfpass</a>
                        </div>

                        <div id="perf-{{ $tabId }}" class="pkg-tab-panel" role="tabpanel" aria-labelledby="perf-{{ $tabId }}-tab" style="display:grid; gap:10px;">
                            @php
                                if (count($performanceClasses) === 0) {
                                    $performanceClasses = [['key' => '', 'name' => '', 'wins_min' => '', 'wins_max' => '']];
                                }
                            @endphp

                            <form method="post" action="{{ route('admin.boxing.settings.update', ['section' => 'performance-classes']) }}" style="display:grid; gap:10px;">
                                @csrf
                                <input type="hidden" name="package_key" value="{{ $packageKey }}">

                                <div style="display:grid; grid-template-columns:1fr 1.8fr 1fr 1fr auto; gap:8px; font-size:12px; color:var(--ink-soft);">
                                    <div>Key</div>
                                    <div>Name</div>
                                    <div>Siege ab</div>
                                    <div>Siege bis</div>
                                    <div></div>
                                </div>

                                @foreach ($performanceClasses as $class)
                                    <div data-row style="display:grid; grid-template-columns:1fr 1.8fr 1fr 1fr auto; gap:8px;">
                                        <input name="class_key[]" value="{{ $class['key'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="z. B. C">
                                        <input name="class_name[]" value="{{ $class['name'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="Leistungsklasse">
                                        <input name="wins_min[]" type="number" min="0" value="{{ $class['wins_min'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="min">
                                        <input name="wins_max[]" type="number" min="0" value="{{ $class['wins_max'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="max">
                                        <button type="button" onclick="removeClassRow(this)" style="border:1px solid #e2a29a; color:#7d2c1f; background:#fff0ed; border-radius:10px; padding:0 10px;">X</button>
                                    </div>
                                @endforeach

                                <div data-row style="display:grid; grid-template-columns:1fr 1.8fr 1fr 1fr auto; gap:8px;">
                                    <input name="class_key[]" style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="neuer Key">
                                    <input name="class_name[]" style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="neuer Name">
                                    <input name="wins_min[]" type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="min">
                                    <input name="wins_max[]" type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="max">
                                    <button type="button" onclick="removeClassRow(this)" style="border:1px solid #e2a29a; color:#7d2c1f; background:#fff0ed; border-radius:10px; padding:0 10px;">X</button>
                                </div>

                                <div>
                                    <button class="logout" type="submit" style="background:#016734;">Leistungsklassen speichern</button>
                                </div>
                            </form>
                        </div>

                        <div id="age-{{ $tabId }}" class="pkg-tab-panel" role="tabpanel" aria-labelledby="age-{{ $tabId }}-tab" hidden style="display:none; gap:12px;">
                            @php
                            @endphp

                            {{-- Altersklassen-Formular (dict-Struktur: Code → alter/sex/time/break/rounds) --}}
                            <form method="post" action="{{ route('admin.boxing.settings.update', ['section' => 'age-classes']) }}" style="display:grid; gap:10px;">
                                @csrf
                                <input type="hidden" name="package_key" value="{{ $packageKey }}">

                                <div style="display:grid; grid-template-columns:80px 1fr 55px 75px auto; gap:8px; font-size:12px; color:var(--ink-soft);">
                                    <div>Code</div>
                                    <div>Name</div>
                                    <div>Gesch.</div>
                                    <div>Alter max</div>
                                    <div></div>
                                </div>

                                @foreach ($ageClasses as $ageCode => $ageData)
                                    @php
                                        $acSex    = $ageData['sex'] ?? 'm';
                                        $acRounds = (array) ($ageData['rounds'] ?? []);
                                        $acTime   = (array) ($ageData['time']   ?? []);
                                        $acBreak  = (array) ($ageData['break']  ?? []);
                                    @endphp
                                    <div data-row style="border:1px solid var(--line); border-radius:10px; padding:8px; display:grid; gap:6px;">
                                        <div style="display:grid; grid-template-columns:80px 1fr 55px 75px auto; gap:8px;">
                                            <input name="class_code[]" value="{{ $ageCode }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="Code">
                                            <input name="class_name[]" value="{{ $ageData['name'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="Name">
                                            <select name="class_sex[]" style="border:1px solid var(--line); border-radius:10px; padding:9px; background:#fff;">
                                                <option value="m" {{ $acSex === 'm' ? 'selected' : '' }}>m</option>
                                                <option value="w" {{ $acSex === 'w' ? 'selected' : '' }}>w</option>
                                            </select>
                                            <input name="class_alter[]" type="number" min="0" value="{{ $ageData['alter'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="Alter">
                                            <button type="button" onclick="removeClassRow(this)" style="border:1px solid #e2a29a; color:#7d2c1f; background:#fff0ed; border-radius:10px; padding:0 10px;">X</button>
                                        </div>
                                        <div style="display:grid; grid-template-columns:55px repeat(9, 1fr); gap:4px; font-size:11px; color:var(--ink-soft); padding-left:2px;">
                                            <div></div>
                                            <div style="text-align:center;">Rnd A</div><div style="text-align:center;">Rnd B</div><div style="text-align:center;">Rnd C</div>
                                            <div style="text-align:center;">Zeit A</div><div style="text-align:center;">Zeit B</div><div style="text-align:center;">Zeit C</div>
                                            <div style="text-align:center;">Pse A</div><div style="text-align:center;">Pse B</div><div style="text-align:center;">Pse C</div>
                                        </div>
                                        <div style="display:grid; grid-template-columns:55px repeat(9, 1fr); gap:4px;">
                                            <div style="font-size:11px; color:var(--ink-soft); display:flex; align-items:center;">Turnier</div>
                                            <input name="rounds_A[]" type="number" min="0" value="{{ $acRounds['A'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                            <input name="rounds_B[]" type="number" min="0" value="{{ $acRounds['B'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                            <input name="rounds_C[]" type="number" min="0" value="{{ $acRounds['C'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                            <input name="time_A[]"   type="number" min="0" value="{{ $acTime['A'] ?? '' }}"   style="border:1px solid var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                            <input name="time_B[]"   type="number" min="0" value="{{ $acTime['B'] ?? '' }}"   style="border:1px solid var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                            <input name="time_C[]"   type="number" min="0" value="{{ $acTime['C'] ?? '' }}"   style="border:1px solid var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                            <input name="break_A[]"  type="number" min="0" value="{{ $acBreak['A'] ?? '' }}"  style="border:1px solid var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                            <input name="break_B[]"  type="number" min="0" value="{{ $acBreak['B'] ?? '' }}"  style="border:1px solid var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                            <input name="break_C[]"  type="number" min="0" value="{{ $acBreak['C'] ?? '' }}"  style="border:1px solid var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Neue leere Zeile --}}
                                <div data-row style="border:1px dashed var(--line); border-radius:10px; padding:8px; display:grid; gap:6px;">
                                    <div style="display:grid; grid-template-columns:80px 1fr 55px 75px auto; gap:8px;">
                                        <input name="class_code[]" style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="Code">
                                        <input name="class_name[]" style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="Name">
                                        <select name="class_sex[]" style="border:1px dashed var(--line); border-radius:10px; padding:9px; background:#fff;">
                                            <option value="m">m</option>
                                            <option value="w">w</option>
                                        </select>
                                        <input name="class_alter[]" type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="Alter">
                                        <button type="button" onclick="removeClassRow(this)" style="border:1px solid #e2a29a; color:#7d2c1f; background:#fff0ed; border-radius:10px; padding:0 10px;">X</button>
                                    </div>
                                    <div style="display:grid; grid-template-columns:55px repeat(9, 1fr); gap:4px;">
                                        <div></div>
                                        <input name="rounds_A[]" type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                        <input name="rounds_B[]" type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                        <input name="rounds_C[]" type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                        <input name="time_A[]"   type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                        <input name="time_B[]"   type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                        <input name="time_C[]"   type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                        <input name="break_A[]"  type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                        <input name="break_B[]"  type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                        <input name="break_C[]"  type="number" min="0" style="border:1px dashed var(--line); border-radius:10px; padding:8px 4px; font-size:12px; text-align:center;" placeholder="–">
                                    </div>
                                </div>

                                <div>
                                    <button class="logout" type="submit" style="background:#016734;">Altersklassen speichern</button>
                                </div>
                            </form>

                            {{-- Gewichtsklassen je Altersklasse (gewicht-dict mit kg-Limit als Key) --}}
                            <div style="display:grid; gap:8px;">
                                <div style="font-weight:700;">Gewichtsklassen je Altersklasse</div>
                                <form method="post" action="{{ route('admin.boxing.settings.update', ['section' => 'weight-classes']) }}" style="display:grid; gap:10px;">
                                    @csrf
                                    <input type="hidden" name="package_key" value="{{ $packageKey }}">

                                    @foreach ($ageClasses as $ageCode => $ageData)
                                        @php
                                            $acName  = trim((string) ($ageData['name'] ?? $ageCode));
                                            $gewicht = (array) ($ageData['gewicht'] ?? []);
                                        @endphp
                                        <details style="border:1px solid var(--line); border-radius:10px; background:#fff;">
                                            <summary style="cursor:pointer; padding:10px; list-style:none; font-weight:700;">
                                                {{ $acName }}
                                                <span style="font-weight:400; color:var(--ink-soft);">({{ $ageCode }})</span>
                                                <span style="font-weight:400; font-size:12px; color:var(--ink-soft);"> — {{ count($gewicht) }} Klassen</span>
                                            </summary>
                                            <div style="padding:0 10px 10px; display:grid; gap:8px;">
                                                <div style="display:grid; grid-template-columns:70px 1.8fr 70px 1.2fr auto; gap:8px; font-size:12px; color:var(--ink-soft);">
                                                    <div>kg-Limit</div>
                                                    <div>Name</div>
                                                    <div>Kurzform</div>
                                                    <div>Handschuhe</div>
                                                    <div></div>
                                                </div>

                                                @foreach ($gewicht as $kg => $w)
                                                    <div data-row style="display:grid; grid-template-columns:70px 1.8fr 70px 1.2fr auto; gap:8px;">
                                                        <input type="hidden" name="age_code[]" value="{{ $ageCode }}">
                                                        <input name="weight_kg[]"    value="{{ $kg }}"             style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="kg">
                                                        <input name="weight_name[]"  value="{{ $w['name']  ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="Name">
                                                        <input name="weight_short[]" value="{{ $w['short'] ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="Kurz">
                                                        <input name="weight_note[]"  value="{{ $w['note']  ?? '' }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="Handschuhe">
                                                        <button type="button" onclick="removeClassRow(this)" style="border:1px solid #e2a29a; color:#7d2c1f; background:#fff0ed; border-radius:10px; padding:0 10px;">X</button>
                                                    </div>
                                                @endforeach

                                                <div data-row style="display:grid; grid-template-columns:70px 1.8fr 70px 1.2fr auto; gap:8px;">
                                                    <input type="hidden" name="age_code[]" value="{{ $ageCode }}">
                                                    <input name="weight_kg[]"    style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="kg">
                                                    <input name="weight_name[]"  style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="Name">
                                                    <input name="weight_short[]" style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="Kurz">
                                                    <input name="weight_note[]"  style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="Handschuhe">
                                                    <button type="button" onclick="removeClassRow(this)" style="border:1px solid #e2a29a; color:#7d2c1f; background:#fff0ed; border-radius:10px; padding:0 10px;">X</button>
                                                </div>
                                            </div>
                                        </details>
                                    @endforeach

                                    <div>
                                        <button class="logout" type="submit" style="background:#016734;">Gewichtsklassen speichern</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="pass-{{ $tabId }}" class="pkg-tab-panel" role="tabpanel" aria-labelledby="pass-{{ $tabId }}-tab" hidden style="display:none; gap:12px;">
                            @php
                                $passKeywords = array_values(array_filter((array) ($package['pass_keywords'] ?? []), fn ($keyword) => is_string($keyword) && trim($keyword) !== ''));
                                if (count($passKeywords) === 0) {
                                    $passKeywords = ['Arzt gültig bis', 'KO-Sperre gültig bis', 'Registrierung gültig bis'];
                                }
                            @endphp
                            <form method="post" action="{{ route('admin.boxing.settings.update', ['section' => 'pass-keywords']) }}" style="display:grid; gap:10px;">
                                @csrf
                                <input type="hidden" name="package_key" value="{{ $packageKey }}">

                                @foreach ($passKeywords as $keyword)
                                    <div data-row style="display:grid; grid-template-columns:1fr auto; gap:8px;">
                                        <input name="pass_keyword[]" value="{{ $keyword }}" style="border:1px solid var(--line); border-radius:10px; padding:9px;" placeholder="Stichwort">
                                        <button type="button" onclick="removeClassRow(this)" style="border:1px solid #e2a29a; color:#7d2c1f; background:#fff0ed; border-radius:10px; padding:0 10px;">X</button>
                                    </div>
                                @endforeach

                                <div data-row style="display:grid; grid-template-columns:1fr auto; gap:8px;">
                                    <input name="pass_keyword[]" style="border:1px dashed var(--line); border-radius:10px; padding:9px;" placeholder="neues Stichwort">
                                    <button type="button" onclick="removeClassRow(this)" style="border:1px solid #e2a29a; color:#7d2c1f; background:#fff0ed; border-radius:10px; padding:0 10px;">X</button>
                                </div>

                                <div>
                                    <button class="logout" type="submit" style="background:#016734;">Kampfpass-Stichworte speichern</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </details>
            @empty
                <div style="color:var(--ink-soft);">Noch keine Pakete vorhanden.</div>
            @endforelse
        </div>
    </div>

    <script>
        function removeClassRow(button) {
            var row = button.closest('[data-row]');
            if (row) {
                row.remove();
            }
        }

        var activatePackageTab = function (button) {
            var details = button.closest('details');
            if (!details) {
                return;
            }

            details.querySelectorAll('.pkg-tab-btn').forEach(function (btn) {
                var isActive = btn === button;
                btn.classList.toggle('active', isActive);
                btn.style.background = isActive ? '#fff' : '#f6f8f7';
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                btn.setAttribute('tabindex', isActive ? '0' : '-1');
            });

            details.querySelectorAll('.pkg-tab-panel').forEach(function (panel) {
                var isActive = panel.id === button.getAttribute('data-tab-target');
                panel.style.display = isActive ? 'grid' : 'none';
                panel.hidden = !isActive;
            });
        };

        document.querySelectorAll('.pkg-tab-btn').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                activatePackageTab(button);
            });

            button.addEventListener('keydown', function (event) {
                if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft' && event.key !== 'Home' && event.key !== 'End') {
                    return;
                }

                event.preventDefault();

                var tabs = Array.prototype.slice.call(button.closest('[role="tablist"]')?.querySelectorAll('.pkg-tab-btn') || []);
                var currentIndex = tabs.indexOf(button);
                if (currentIndex === -1 || tabs.length === 0) {
                    return;
                }

                var nextIndex = currentIndex;
                if (event.key === 'Home') {
                    nextIndex = 0;
                } else if (event.key === 'End') {
                    nextIndex = tabs.length - 1;
                } else if (event.key === 'ArrowRight') {
                    nextIndex = (currentIndex + 1) % tabs.length;
                } else if (event.key === 'ArrowLeft') {
                    nextIndex = (currentIndex - 1 + tabs.length) % tabs.length;
                }

                tabs[nextIndex].focus();
                activatePackageTab(tabs[nextIndex]);
            });
        });
    </script>
@endsection
