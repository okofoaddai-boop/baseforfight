@extends('admin.layout')

@section('title', 'Clubs & Anfragen | BaseForFight')

@section('content')
    <div class="header">
        <div>
            <h1>Vereinssteuerung</h1>
            <div style="color:var(--ink-soft);"><img class="inline-illustration" src="{{ asset('assets/brand/icons/icon_group.png') }}" alt="Boxergruppe fuer Vereinsarbeit">Eigene Vereine, Mitglieder und offene Beitrittsanfragen.</div>
        </div>
    </div>

    @if (session('status'))
        <div class="card" style="margin-bottom:16px; border-color: #7db928; background:#eff7ea;">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid" style="margin-bottom:18px;">
        <div class="card" style="grid-column: span 6;">
            <h2 style="margin-top:0;">{{ $isSuperAdmin ? 'Vereine (Support-Simulation)' : 'Meine Vereine' }}</h2>
            @forelse ($clubs as $club)
                <div style="display:flex; justify-content:space-between; gap:10px; margin-bottom:10px; padding:10px 12px; border:1px solid var(--line); border-radius:12px; background:var(--panel);">
                    <div>
                        <strong>{{ $club->name }}</strong><br>
                        <span style="color:var(--ink-soft); font-size:13px;">Slug: {{ $club->slug }}</span>
                    </div>

                    @if ($isSuperAdmin)
                        @php
                            $clubMembers = $members->where('club_id', $club->id);
                            $hasManager = $clubMembers->contains(fn ($member) => in_array($member->role, ['manager', 'owner', 'admin'], true));
                            $hasTrainer = $clubMembers->contains(fn ($member) => in_array($member->role, ['trainer', 'coach'], true));
                        @endphp
                        <div style="display:flex; gap:8px; align-items:center;">
                            <form method="post" action="{{ route('admin.impersonate.club-role', ['club' => $club, 'role' => 'manager']) }}">
                                @csrf
                                <button class="logout" type="submit" style="background:#016734;" @if (! $hasManager) disabled @endif>als Manager</button>
                            </form>
                            <form method="post" action="{{ route('admin.impersonate.club-role', ['club' => $club, 'role' => 'trainer']) }}">
                                @csrf
                                <button class="logout" type="submit" style="background:#7db928; color:#1b2d1f;" @if (! $hasTrainer) disabled @endif>als Trainer</button>
                            </form>
                        </div>
                    @else
                        <strong style="text-transform:uppercase; font-size:12px; color:var(--accent-2);">Manager-Bereich</strong>
                    @endif
                </div>
            @empty
                <p style="color:var(--ink-soft);">Du verwaltest aktuell keinen Verein.</p>
            @endforelse
        </div>

        <div class="card" style="grid-column: span 6;">
            <h2 style="margin-top:0;">Offene Beitrittsanfragen</h2>
            @forelse ($joinRequests as $request)
                <div style="display:flex; justify-content:space-between; gap:10px; margin-bottom:10px; padding:10px 12px; border:1px solid var(--line); border-radius:12px; background:var(--panel); align-items:center;">
                    <div>
                        <strong>{{ $request->user->name }}</strong><br>
                        <span style="color:var(--ink-soft); font-size:13px;">{{ $request->user->email }} · Verein: {{ $request->requested_club_name }}</span>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <form method="post" action="{{ route('admin.club-join-requests.approve', $request) }}">
                            @csrf
                            <button class="logout" type="submit" style="background:#016734;">Freigeben</button>
                        </form>
                        <form method="post" action="{{ route('admin.club-join-requests.decline', $request) }}">
                            @csrf
                            <button class="logout" type="submit" style="background:#dd6850;">Ablehnen</button>
                        </form>
                    </div>
                </div>
            @empty
                <p style="color:var(--ink-soft);">Keine offenen Anfragen vorhanden.</p>
            @endforelse
        </div>
    </section>

    <section class="card">
        <h2 style="margin-top:0;">Mitglieder</h2>
        @forelse ($members as $member)
            <div style="display:grid; grid-template-columns: 1.3fr 1fr 0.6fr 0.8fr; gap:10px; margin-bottom:10px; padding:10px 12px; border:1px solid var(--line); border-radius:12px; background:var(--panel);">
                <div><strong>{{ $member->user_name }}</strong><br><span style="color:var(--ink-soft); font-size:13px;">{{ $member->club_name }}</span></div>
                <div>{{ $member->email }}</div>
                <div style="text-transform:uppercase; color:var(--green-strong); font-weight:700;">{{ $member->role }}</div>
                <div style="color:var(--ink-soft);">{{ $member->joined_at }}</div>
            </div>
        @empty
            <p style="color:var(--ink-soft);">Noch keine Mitglieder geladen.</p>
        @endforelse
    </section>
@endsection
