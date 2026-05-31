@extends('admin.layout')

@section('title', 'Clubs & Anfragen | BaseForFight')

@section('content')
    <div class="header">
        <div>
            <h1>Vereinssteuerung</h1>
            <div style="color:var(--ink-soft);"><img class="inline-illustration" src="{{ asset('assets/brand/icons/icon_group.png') }}" alt="Boxergruppe">Vereine, Mitglieder und offene Beitrittsanfragen verwalten.</div>
        </div>
    </div>

    @if (session('status'))
        <div class="card" style="margin-bottom:16px; border-color: #7db928; background:#eff7ea;">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="card" style="margin-bottom:16px; border-color: var(--danger); background:#fdf1ef; color:var(--danger);">{{ $errors->first() }}</div>
    @endif

    {{-- Offene Beitrittsanfragen --}}
    <section class="card" style="margin-bottom:18px;">
        <h2 style="margin-top:0;">Offene Beitrittsanfragen</h2>
        @forelse ($joinRequests as $req)
            <div style="display:flex; justify-content:space-between; gap:10px; margin-bottom:10px; padding:10px 12px; border:1px solid var(--line); border-radius:12px; background:var(--panel); align-items:center; flex-wrap:wrap;">
                <div>
                    <strong>{{ $req->user->name }}</strong>
                    <span style="color:var(--ink-soft); font-size:13px;"> &middot; {{ $req->user->email }}</span><br>
                    <span style="color:var(--ink-soft); font-size:13px;">Verein: {{ $req->club?->name ?? $req->requested_club_name }}</span>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <form method="post" action="{{ route('admin.club-join-requests.approve', $req) }}">
                        @csrf
                        <button class="logout" type="submit" style="background:#016734;">Freigeben</button>
                    </form>
                    <form method="post" action="{{ route('admin.club-join-requests.decline', $req) }}">
                        @csrf
                        <button class="logout" type="submit" style="background:#dd6850;">Ablehnen</button>
                    </form>
                </div>
            </div>
        @empty
            <p style="color:var(--ink-soft);">Keine offenen Anfragen.</p>
        @endforelse
    </section>

    {{-- Vereine mit Mitgliedern --}}
    @foreach ($clubs as $club)
        @php $clubMemberships = $memberships->get($club->id, collect()); @endphp
        <section class="card" style="margin-bottom:18px;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
                <div>
                    <h2 style="margin:0;">{{ $club->name }}</h2>
                    <span style="color:var(--ink-soft); font-size:13px;">{{ $club->slug }}</span>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    @if ($isSuperAdmin)
                        <a href="{{ route('clubs.show', $club) }}" class="logout" style="background:#4d6050;">Vereinsbereich</a>
                    @endif
                </div>
            </div>

            {{-- Mitglieder --}}
            @forelse ($clubMemberships as $membership)
                <div style="display:flex; justify-content:space-between; gap:10px; margin-bottom:10px; padding:10px 12px; border:1px solid var(--line); border-radius:12px; background:var(--panel); align-items:flex-start; flex-wrap:wrap;">
                    <div>
                        <strong>{{ $membership->user->name }}</strong>
                        <span style="color:var(--ink-soft); font-size:13px;"> · {{ $membership->user->email }}</span>
                        <div style="margin-top:5px;">
                            @foreach ($membership->roles as $role)
                                @php
                                    $badgeColor = match($role->role) {
                                        'club_manager'  => '#016734',
                                        'event_manager' => '#7db928',
                                        'trainer'       => '#4d6050',
                                        default         => '#999',
                                    };
                                @endphp
                                <span style="display:inline-block; font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px; background:{{ $badgeColor }}; color:#fff; margin-right:4px;">{{ $roleLabels[$role->role] ?? $role->role }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                        {{-- Rollen bearbeiten --}}
                        <form method="post" action="{{ route('admin.clubs.members.update-roles', ['club' => $club->slug, 'member' => $membership->user_id]) }}" style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                            @csrf
                            @foreach ($allRoles as $role)
                                <label style="font-size:12px; display:flex; gap:4px; align-items:center; cursor:pointer;">
                                    <input type="checkbox" name="roles[]" value="{{ $role }}"
                                        @checked($membership->roles->pluck('role')->contains($role))>
                                    {{ $roleLabels[$role] ?? $role }}
                                </label>
                            @endforeach
                            <button type="submit" class="logout" style="background:#7db928; color:#1b2d1f; padding:6px 12px; font-size:12px;">Rollen speichern</button>
                        </form>
                        {{-- Mitglied entfernen --}}
                        <form method="post" action="{{ route('admin.clubs.members.remove', ['club' => $club->slug, 'member' => $membership->user_id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="logout" style="background:#dd6850; padding:6px 12px; font-size:12px;"
                                onclick="return confirm('Mitglied wirklich entfernen?')">Entfernen</button>
                        </form>
                    </div>
                </div>
            @empty
                <p style="color:var(--ink-soft); font-size:14px;">Noch keine Mitglieder in diesem Verein.</p>
            @endforelse

            {{-- Superadmin: User zuweisen --}}
            @if ($isSuperAdmin)
                <hr style="border:none; border-top:1px solid var(--line); margin:14px 0;">
                <form method="post" action="{{ route('admin.clubs.members.assign', ['club' => $club->slug]) }}" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
                    @csrf
                    <div style="flex:1; min-width:180px;">
                        <label style="font-size:13px; font-weight:700; display:block; margin-bottom:4px;">User hinzufuegen</label>
                        <select name="user_id" required style="width:100%; border:1px solid var(--line); border-radius:8px; padding:7px 10px; font:inherit; background:#fff;">
                            <option value="">– User auswählen –</option>
                            @foreach ($allUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:700; display:block; margin-bottom:4px;">Rollen</label>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            @foreach ($allRoles as $role)
                                <label style="font-size:12px; display:flex; gap:4px; align-items:center; cursor:pointer;">
                                    <input type="checkbox" name="roles[]" value="{{ $role }}">
                                    {{ $roleLabels[$role] ?? $role }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="logout" style="background:#016734;">Zuweisen</button>
                </form>
            @endif
        </section>
    @endforeach

    @if ($clubs->isEmpty())
        <div class="card"><p style="color:var(--ink-soft);">Keine Vereine gefunden.</p></div>
    @endif
@endsection
