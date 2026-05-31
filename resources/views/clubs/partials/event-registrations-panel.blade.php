@php
    $statusLabels = [
        \App\Models\Registration::STATUS_ACTIVE => 'Teilnahme',
        \App\Models\Registration::STATUS_WAITING => 'Wartend',
        \App\Models\Registration::STATUS_WITHDRAWN => 'Zurückgezogen',
    ];
    $manageUrl = route('clubs.events.registrations.manage', ['club' => $club, 'event' => $event]);
@endphp

<div class="management-topline">
    <div>
        <h3 style="margin:0;">Veranstaltersteuerung</h3>
        <div class="muted">Status und Abrechnung bleiben getrennt. Bereits gesicherte Abrechnungen werden durch spätere Rückzüge nicht reduziert.</div>
    </div>
    <a class="btn" href="{{ route('events.registrations.export', $event) }}">CSV herunterladen</a>
</div>

<div data-registration-feedback></div>

<div class="management-kpis">
    <div class="kpi-card"><div class="muted">Aktiv</div><strong>{{ $registrationStats['active'] ?? 0 }}</strong></div>
    <div class="kpi-card"><div class="muted">Wartend</div><strong>{{ $registrationStats['waiting'] ?? 0 }}</strong></div>
    <div class="kpi-card"><div class="muted">Zurückgezogen</div><strong>{{ $registrationStats['withdrawn'] ?? 0 }}</strong></div>
    <div class="kpi-card"><div class="muted">Abrechenbar</div><strong>{{ $registrationStats['billable'] ?? 0 }}</strong></div>
</div>

<div class="registration-toolbar" data-registration-filter-form>
    <div class="form-row">
        <label for="registration_status_{{ $event->getKey() }}">Status</label>
        <select id="registration_status_{{ $event->getKey() }}" name="registration_status">
            <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Alle</option>
            <option value="{{ \App\Models\Registration::STATUS_ACTIVE }}" @selected(($filters['status'] ?? '') === \App\Models\Registration::STATUS_ACTIVE)>Teilnahme</option>
            <option value="{{ \App\Models\Registration::STATUS_WAITING }}" @selected(($filters['status'] ?? '') === \App\Models\Registration::STATUS_WAITING)>Wartend</option>
            <option value="{{ \App\Models\Registration::STATUS_WITHDRAWN }}" @selected(($filters['status'] ?? '') === \App\Models\Registration::STATUS_WITHDRAWN)>Zurückgezogen</option>
        </select>
    </div>

    <div class="form-row">
        <label for="registration_q_{{ $event->getKey() }}">Freitext</label>
        <input id="registration_q_{{ $event->getKey() }}" type="search" name="registration_q" value="{{ $filters['query'] ?? '' }}" placeholder="Name, Verein oder Trainer">
    </div>

    <div class="form-row">
        <label for="registration_group_{{ $event->getKey() }}">Gruppieren nach</label>
        <select id="registration_group_{{ $event->getKey() }}" name="registration_group">
            <option value="club" @selected(($filters['group'] ?? 'club') === 'club')>Verein</option>
            <option value="weight" @selected(($filters['group'] ?? '') === 'weight')>Gewichtsklasse</option>
        </select>
    </div>

    <div class="form-row">
        <label for="registration_sort_{{ $event->getKey() }}">Sortieren nach</label>
        <select id="registration_sort_{{ $event->getKey() }}" name="registration_sort">
            <option value="weight_class" @selected(($filters['sort'] ?? 'weight_class') === 'weight_class')>Gewichtsklasse</option>
            <option value="club" @selected(($filters['sort'] ?? '') === 'club')>Verein</option>
            <option value="fighter" @selected(($filters['sort'] ?? '') === 'fighter')>Kämpfer</option>
            <option value="changed_at" @selected(($filters['sort'] ?? '') === 'changed_at')>Zuletzt geändert</option>
        </select>
    </div>

    <div class="registration-toolbar-actions">
        <button class="btn btn-soft" type="button" data-registration-reset>Zurücksetzen</button>
    </div>

    <div class="registration-toolbar-actions">
        <button class="btn" type="button" data-registration-apply>Anwenden</button>
    </div>
</div>

<div class="registration-results-meta">
    <span>{{ $filteredCount }} von {{ $totalCount }} Meldungen sichtbar</span>
    <span>Gruppierung: {{ ($filters['group'] ?? 'club') === 'weight' ? 'Gewichtsklasse' : 'Verein' }}</span>
</div>

@forelse ($groupedRegistrations as $groupLabel => $groupRegistrations)
    <article class="registration-group-card">
        <div class="registration-group-header">
            <div>
                <strong>{{ $groupLabel }}</strong>
                <div class="muted">{{ $groupRegistrations->count() }} Meldungen</div>
            </div>

            <div class="table-actions">
                <select data-registration-group-status>
                    <option value="{{ \App\Models\Registration::STATUS_ACTIVE }}">Teilnahme</option>
                    <option value="{{ \App\Models\Registration::STATUS_WAITING }}">Wartend</option>
                    <option value="{{ \App\Models\Registration::STATUS_WITHDRAWN }}">Zurückgezogen</option>
                </select>
                <button
                    class="btn btn-soft"
                    type="button"
                    data-registration-group-submit
                    data-manage-url="{{ $manageUrl }}"
                    data-reason="club_portal_batch_group"
                    data-registration-ids="{{ $groupRegistrations->pluck('id')->implode(',') }}"
                >Gruppe setzen</button>
            </div>
        </div>

        <div class="table-wrap">
            <table class="registration-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Kämpfer</th>
                        <th>Gewicht</th>
                        <th>Status</th>
                        <th>Abrechnung</th>
                        <th>Verein</th>
                        <th>Trainer</th>
                        <th>Geändert</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groupRegistrations as $registration)
                        @php
                            $snapshot = (array) ($registration->fighter_snapshot ?? []);
                            $classes = (array) ($snapshot['classes'] ?? []);
                            $weight = data_get($snapshot, 'weight.weight_kg');
                        @endphp
                        <tr>
                            <td><input type="checkbox" value="{{ $registration->getKey() }}" data-registration-checkbox></td>
                            <td>
                                <strong>{{ trim(($registration->fighter?->first_name ?? '') . ' ' . ($registration->fighter?->last_name ?? '')) ?: '-' }}</strong>
                                <div class="muted">{{ $classes['age'] ?? '-' }} | {{ $classes['performance'] ?? '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $classes['weight'] ?? 'Ohne Gewichtsklasse' }}</div>
                                <div class="muted">{{ is_numeric($weight) ? number_format((float) $weight, 1, ',', '.') . ' kg' : '-' }}</div>
                            </td>
                            <td><span class="status-badge {{ $registration->status }}">{{ $statusLabels[$registration->status] ?? $registration->status }}</span></td>
                            <td>
                                @if ($registration->billable_at)
                                    <span class="billable-flag">seit {{ $registration->billable_at->format('d.m.Y H:i') }}</span>
                                @else
                                    <span class="muted">noch nicht abrechenbar</span>
                                @endif
                            </td>
                            <td>{{ $registration->fighter?->club?->name ?? 'Ohne Verein' }}</td>
                            <td>{{ $registration->registeredBy?->name ?? $registration->registeredBy?->email ?? '-' }}</td>
                            <td>{{ $registration->status_changed_at?->format('d.m.Y H:i') ?? $registration->updated_at?->format('d.m.Y H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-actions">
            <span class="muted">Eine einzelne Meldung bearbeitest du, indem du genau eine Zeile auswählst.</span>
            <div class="table-actions">
                <select data-registration-selection-status>
                    <option value="{{ \App\Models\Registration::STATUS_ACTIVE }}">Teilnahme</option>
                    <option value="{{ \App\Models\Registration::STATUS_WAITING }}">Wartend</option>
                    <option value="{{ \App\Models\Registration::STATUS_WITHDRAWN }}">Zurückgezogen</option>
                </select>
                <button
                    class="btn"
                    type="button"
                    data-registration-selection-submit
                    data-manage-url="{{ $manageUrl }}"
                    data-reason="club_portal_batch_selection"
                >Auswahl aktualisieren</button>
            </div>
        </div>
    </article>
@empty
    <div class="registration-loading">Keine Meldungen für diese Filterung gefunden.</div>
@endforelse