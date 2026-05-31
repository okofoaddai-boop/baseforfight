<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use App\Services\ClubPermissionService;
use App\Services\RegistrationWorkflowService;
use App\Services\Modules\BoxingSettingsStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicEventController extends Controller
{
    public function __construct(
        private readonly BoxingSettingsStore $boxingSettingsStore,
        private readonly ClubPermissionService $clubPermissions,
        private readonly RegistrationWorkflowService $registrationWorkflow,
    ) {
    }

    public function index(Request $request): View
    {
        $query = Event::query()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at');

        $user = $request->user();
        $isPrivileged = $user !== null && $user->isPlatformAdmin();
        $userClubs = collect();

        if ($user) {
            $userClubs = ClubMembership::query()
                ->with(['club' => fn ($q) => $q->withCount('fighters'), 'roles'])
                ->where('user_id', $user->getKey())
                ->whereHas('club')
                ->get()
                ->map(function (ClubMembership $membership) {
                    $club = $membership->club;
                    $roleNames = $membership->roles->pluck('role')->all();
                    $roleLabels = [
                        ClubMembershipRole::ROLE_CLUB_MANAGER => __('Club-Manager'),
                        ClubMembershipRole::ROLE_EVENT_MANAGER => __('Veranstaltungsmanager'),
                        ClubMembershipRole::ROLE_TRAINER => __('Trainer'),
                    ];

                    $club->setAttribute('user_role_label', implode(', ', array_map(
                        static fn (string $role): string => $roleLabels[$role] ?? $role,
                        $roleNames
                    )));
                    return $club;
                })
                ->sortBy('name')
                ->values();
        }

        if (! $isPrivileged) {
            $query->where('status', 'published');
        }

        $events = $query->limit(12)->get();

        $now = now();
        $events = $events->map(function (Event $event) use ($now): Event {
            $isEnded = $this->isEventEnded($event, $now);
            $event->setAttribute('display_status', $event->status === 'draft' ? __('Entwurf') : ($isEnded ? __('Beendet') : null));

            return $event;
        });

        return view('welcome', [
            'events' => $events,
            'isPrivilegedView' => $isPrivileged,
            'userClubs' => $userClubs,
            'isImpersonating' => $request->session()->has('impersonator_id'),
        ]);
    }

    public function show(Request $request, Event $event): View
    {
        $user = $request->user();
        $isPrivileged = $user !== null && $user->isPlatformAdmin();

        if (! $isPrivileged && $event->getAttribute('status') !== 'published') {
            abort(404);
        }

        $this->registrationWorkflow->lockBillingForEvent($event);
        $event->refresh();

        $registrationDeadline = $event->registration_deadline;
        $now = now();
        $isEnded = $this->isEventEnded($event, $now);
        $isDeadlinePassed = $this->registrationWorkflow->hasDeadlinePassed($event);
        $canSubmitRegistrations = $event->status === 'published'
            && $event->cancelled_at === null
            && ! $isEnded;

        $displayStatus = $event->status === 'draft' ? __('Entwurf') : ($isEnded ? __('Beendet') : null);

        $boxingPackages = $this->boxingSettingsStore->readAllPackages();
        $boxingPackageKey = trim((string) ($event->boxing_package_key ?? ''));
        if ($boxingPackageKey === '') {
            $boxingPackageKey = $this->boxingSettingsStore->readActivePackage();
        }
        $boxingPackage = array_key_exists($boxingPackageKey, $boxingPackages)
            ? (array) $boxingPackages[$boxingPackageKey]
            : [];

        $manageableClubIds = $user ? $this->manageableAthleteClubIds($user) : [];
        $canManageOwnRegistrations = count($manageableClubIds) > 0;
        $canManageEventRegistrations = $user !== null && $this->canManageEventRegistrations($user, $event);

        $eligibleFighters = collect();
        $registrationByFighterId = collect();
        $fighterSnapshots = [];

        if (count($manageableClubIds) > 0) {
            $fighterQuery = Fighter::query()
                ->with('club')
                ->whereIn('club_id', $manageableClubIds)
                ->where('status', 'active')
                ->orderBy('last_name')
                ->orderBy('first_name');

            $sportModule = trim((string) ($event->sport_module ?? ''));
            if ($sportModule !== '') {
                $fighterQuery->whereJsonContains('sport_modules', $sportModule);
            }

            $candidateFighters = $fighterQuery->get();

            $registrationByFighterId = Registration::query()
                ->where('event_id', $event->getKey())
                ->whereIn('fighter_id', $candidateFighters->pluck('id')->all())
                ->get()
                ->keyBy('fighter_id');

            foreach ($candidateFighters as $fighter) {
                $snapshot = $this->buildFighterSnapshotForEvent($fighter, $event, $boxingPackage);
                if (! ($snapshot['eligible'] ?? false) && ! $registrationByFighterId->has($fighter->getKey())) {
                    continue;
                }
                $eligibleFighters->push($fighter);
                $fighterSnapshots[(int) $fighter->getKey()] = $snapshot;
            }
        }

        $registeredFighterIds = $registrationByFighterId
            ->filter(fn (Registration $registration) => ! $registration->isWithdrawn())
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();
        $registeredFighters = $eligibleFighters
            ->filter(fn (Fighter $fighter) => in_array((int) $fighter->getKey(), $registeredFighterIds, true))
            ->values();
        $possibleFighters = $canSubmitRegistrations
            ? $eligibleFighters
                ->filter(fn (Fighter $fighter) => ! in_array((int) $fighter->getKey(), $registeredFighterIds, true))
                ->values()
            : collect();

        return view('events.show', [
            'event' => $event,
            'isPrivilegedView' => $isPrivileged,
            'eligibleFighters' => $eligibleFighters,
            'registeredFighters' => $registeredFighters,
            'possibleFighters' => $possibleFighters,
            'registrationByFighterId' => $registrationByFighterId,
            'fighterSnapshots' => $fighterSnapshots,
            'canSubmitRegistrations' => $canSubmitRegistrations,
            'canManageOwnRegistrations' => $canManageOwnRegistrations,
            'isDeadlinePassed' => $isDeadlinePassed,
            'canManageEventRegistrations' => $canManageEventRegistrations,
            'displayStatus' => $displayStatus,
            'boxingPackageKey' => $boxingPackageKey,
            'boxingPackage' => $boxingPackage,
        ]);
    }

    public function showDocument(Request $request, Event $event, int $documentIndex): View
    {
        $user = $request->user();
        $isPrivileged = $user !== null && $user->isPlatformAdmin();

        if (! $isPrivileged && $event->getAttribute('status') !== 'published') {
            abort(404);
        }

        $documents = is_array($event->info_documents) ? array_values($event->info_documents) : [];
        if (! array_key_exists($documentIndex, $documents)) {
            abort(404);
        }

        $storagePath = $this->resolvePublicStoragePath((string) $documents[$documentIndex]);
        if ($storagePath === null || ! Storage::disk('public')->exists($storagePath)) {
            abort(404);
        }

        $pdfPath = Storage::disk('public')->path($storagePath);
        $pdfContents = file_get_contents($pdfPath);
        if ($pdfContents === false) {
            abort(404);
        }

        return view('events.document-preview', [
            'event' => $event,
            'documentName' => basename($storagePath),
            'pdfDataUri' => 'data:application/pdf;base64,' . base64_encode($pdfContents),
        ]);
    }

    public function syncRegistrations(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if ($event->status !== 'published' || $event->cancelled_at !== null) {
            return redirect()
                ->route('events.show', ['event' => $event, 'tab' => 'registrations'])
                ->withErrors(['Die Veranstaltung ist nicht mehr zur Meldung freigegeben.']);
        }

        if ($this->isEventEnded($event, now())) {
            return redirect()
                ->route('events.show', ['event' => $event, 'tab' => 'registrations'])
                ->withErrors(['Die Veranstaltung ist bereits beendet.']);
        }

        $manageableClubIds = $this->manageableAthleteClubIds($user);

        if (count($manageableClubIds) === 0) {
            return redirect()
                ->route('events.show', ['event' => $event, 'tab' => 'registrations'])
                ->withErrors(['Keine verwaltbaren Vereine für Meldungen verfügbar.']);
        }

        $this->registrationWorkflow->lockBillingForEvent($event);
        $event->refresh();

        $boxingPackages = $this->boxingSettingsStore->readAllPackages();
        $boxingPackageKey = trim((string) ($event->boxing_package_key ?? ''));
        if ($boxingPackageKey === '') {
            $boxingPackageKey = $this->boxingSettingsStore->readActivePackage();
        }
        $boxingPackage = array_key_exists($boxingPackageKey, $boxingPackages)
            ? (array) $boxingPackages[$boxingPackageKey]
            : [];

        $fighterQuery = Fighter::query()
            ->whereIn('club_id', $manageableClubIds)
            ->where('status', 'active');

        $sportModule = trim((string) ($event->sport_module ?? ''));
        if ($sportModule !== '') {
            $fighterQuery->whereJsonContains('sport_modules', $sportModule);
        }

        $eligibleFighterIds = $fighterQuery->get()
            ->filter(function (Fighter $fighter) use ($event, $boxingPackage): bool {
                $snapshot = $this->buildFighterSnapshotForEvent($fighter, $event, $boxingPackage);

                return (bool) ($snapshot['eligible'] ?? false);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $validated = $request->validate([
            'fighter_ids' => ['nullable', 'array'],
            'fighter_ids.*' => ['integer', Rule::in($eligibleFighterIds)],
        ]);

        $selectedFighterIds = array_values(array_unique(array_map('intval', (array) ($validated['fighter_ids'] ?? []))));

        $fightersById = Fighter::query()
            ->whereIn('id', $eligibleFighterIds)
            ->get()
            ->keyBy('id');

        $eligibleRegistrations = Registration::query()
            ->where('event_id', $event->getKey())
            ->whereIn('fighter_id', $eligibleFighterIds)
            ->with('event')
            ->get()
            ->keyBy('fighter_id');

        $activeCount = $this->registrationWorkflow->activeRegistrationCount($event);

        $newlyActive = 0;
        $newlyWaiting = 0;
        $newlyWithdrawn = 0;
        $limitSkipped = 0;

        DB::transaction(function () use (
            $event,
            $selectedFighterIds,
            $eligibleFighterIds,
            $fightersById,
            $eligibleRegistrations,
            $user,
            $boxingPackage,
            &$activeCount,
            &$newlyActive,
            &$newlyWaiting,
            &$newlyWithdrawn,
            &$limitSkipped
        ): void {
            $selectedSet = array_fill_keys($selectedFighterIds, true);

            foreach ($eligibleFighterIds as $fighterId) {
                $existing = $eligibleRegistrations->get($fighterId);
                $isSelected = array_key_exists($fighterId, $selectedSet);

                if (! $isSelected) {
                    if ($existing && ! $existing->isWithdrawn()) {
                        if ($existing->isActive()) {
                            $activeCount = max(0, $activeCount - 1);
                        }
                        $this->registrationWorkflow->transitionStatus(
                            $existing,
                            Registration::STATUS_WITHDRAWN,
                            $user,
                            $this->registrationWorkflow->hasDeadlinePassed($event) ? 'trainer_withdrew_after_deadline' : 'trainer_withdrew_before_deadline',
                            ['source' => 'trainer_sync']
                        );
                        $newlyWithdrawn++;
                    }
                    continue;
                }

                $fighter = $fightersById->get($fighterId);
                if (! $fighter) {
                    continue;
                }

                $snapshot = $this->buildFighterSnapshotForEvent($fighter, $event, $boxingPackage);

                if ($existing) {
                    $existing->fill([
                        'fighter_snapshot' => $snapshot,
                        'notes' => $snapshot['summary'] ?? null,
                        'registered_by_user_id' => $user->getKey(),
                        'status_changed_at' => $existing->status_changed_at ?? now(),
                    ]);
                    $existing->save();

                    if ($existing->isWithdrawn()) {
                        $targetStatus = $this->registrationWorkflow->determineInitialStatus($event, $activeCount);
                        if ($targetStatus === null) {
                            $limitSkipped++;
                            continue;
                        }

                        $this->registrationWorkflow->transitionStatus(
                            $existing,
                            $targetStatus,
                            $user,
                            $this->registrationWorkflow->hasDeadlinePassed($event) ? 'trainer_resubmitted_after_deadline' : 'trainer_resubmitted_before_deadline',
                            ['source' => 'trainer_sync']
                        );

                        if ($targetStatus === Registration::STATUS_ACTIVE) {
                            $activeCount++;
                            $newlyActive++;
                        } else {
                            $newlyWaiting++;
                        }
                    }

                    continue;
                }

                $targetStatus = $this->registrationWorkflow->determineInitialStatus($event, $activeCount);
                if ($targetStatus === null) {
                    $limitSkipped++;
                    continue;
                }

                $registration = Registration::query()->create([
                    'fighter_id' => $fighterId,
                    'event_id' => $event->getKey(),
                    'status' => $targetStatus,
                    'registered_by_user_id' => $user->getKey(),
                    'fighter_snapshot' => $snapshot,
                    'notes' => $snapshot['summary'] ?? null,
                    'status_changed_at' => now(),
                ]);

                $this->registrationWorkflow->markCreated($registration, $user, 'trainer_created_registration', ['source' => 'trainer_sync']);

                if ($targetStatus === Registration::STATUS_ACTIVE) {
                    $activeCount++;
                    $newlyActive++;
                } else {
                    $newlyWaiting++;
                }
            }
        });

        $statusParts = [];
        if ($newlyActive > 0) {
            $statusParts[] = $newlyActive . ' aktiv';
        }
        if ($newlyWaiting > 0) {
            $statusParts[] = $newlyWaiting . ' wartend';
        }
        if ($newlyWithdrawn > 0) {
            $statusParts[] = $newlyWithdrawn . ' zurückgezogen';
        }
        if ($limitSkipped > 0) {
            $statusParts[] = $limitSkipped . ' nicht gemeldet (Limit erreicht)';
        }

        $statusMessage = count($statusParts) > 0
            ? 'Meldungen aktualisiert: ' . implode(', ', $statusParts) . '.'
            : 'Keine Änderungen an den Meldungen.';

        return redirect()
            ->route('events.show', ['event' => $event, 'tab' => 'registrations'])
            ->with('status', $statusMessage);
    }

    public function manageRegistrations(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();
        if (! $user || ! $this->canManageEventRegistrations($user, $event)) {
            abort(403);
        }

        $this->registrationWorkflow->lockBillingForEvent($event);
        $event->refresh();

        $registrationIds = Registration::query()
            ->where('event_id', $event->getKey())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $validated = $request->validate([
            'registration_ids' => ['required', 'array', 'min:1'],
            'registration_ids.*' => ['integer', Rule::in($registrationIds)],
            'status' => ['required', Rule::in([
                Registration::STATUS_ACTIVE,
                Registration::STATUS_WAITING,
                Registration::STATUS_WITHDRAWN,
            ])],
            'reason' => ['nullable', 'string', 'max:100'],
        ]);

        $registrations = Registration::query()
            ->where('event_id', $event->getKey())
            ->whereIn('id', (array) $validated['registration_ids'])
            ->get();

        $changed = 0;
        foreach ($registrations as $registration) {
            $before = (string) $registration->status;
            $this->registrationWorkflow->transitionStatus(
                $registration,
                (string) $validated['status'],
                $user,
                $validated['reason'] ?? 'organizer_status_change',
                ['source' => 'organizer_manage']
            );

            if ($before !== (string) $validated['status']) {
                $changed++;
            }
        }

        return redirect()
            ->route('events.show', ['event' => $event, 'tab' => 'registrations'])
            ->with('status', $changed > 0 ? $changed . ' Meldungen wurden aktualisiert.' : 'Keine Statusänderung erforderlich.');
    }

    public function exportRegistrations(Request $request, Event $event): StreamedResponse
    {
        $user = $request->user();
        if (! $user || ! $this->canManageEventRegistrations($user, $event)) {
            abort(403);
        }

        $this->registrationWorkflow->lockBillingForEvent($event);
        $event->refresh();

        $registrations = Registration::query()
            ->with(['fighter.club', 'registeredBy'])
            ->where('event_id', $event->getKey())
            ->get()
            ->sortBy([
                fn (Registration $registration) => strtolower((string) ($registration->fighter?->club?->name ?? '')),
                fn (Registration $registration) => strtolower((string) ($registration->fighter?->last_name ?? '')),
                fn (Registration $registration) => strtolower((string) ($registration->fighter?->first_name ?? '')),
            ])
            ->values();

        $fileName = 'meldungen-' . $event->getKey() . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($registrations): void {
            $handle = fopen('php://output', 'w');
            if (! is_resource($handle)) {
                return;
            }

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Verein', 'Kämpfer', 'Status', 'Abrechenbar', 'Abrechenbar seit', 'Meldegrund', 'Gemeldet von', 'Zuletzt geändert', 'Notizen'], ';');

            foreach ($registrations as $registration) {
                fputcsv($handle, [
                    (string) ($registration->fighter?->club?->name ?? ''),
                    trim((string) (($registration->fighter?->first_name ?? '') . ' ' . ($registration->fighter?->last_name ?? ''))),
                    (string) $registration->status,
                    $registration->billable_at !== null ? 'ja' : 'nein',
                    $registration->billable_at?->format('Y-m-d H:i:s') ?? '',
                    (string) ($registration->billable_reason ?? ''),
                    (string) ($registration->registeredBy?->name ?? $registration->registeredBy?->email ?? ''),
                    $registration->status_changed_at?->format('Y-m-d H:i:s') ?? '',
                    (string) ($registration->notes ?? ''),
                ], ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildFighterSnapshotForEvent(Fighter $fighter, Event $event, array $boxingPackage = []): array
    {
        $eventDate = $event->starts_at?->toDateString();

        $weightEntry = collect((array) ($fighter->boxing_weight_entries ?? []))
            ->filter(fn ($entry) => is_array($entry) && trim((string) ($entry['date'] ?? '')) !== '')
            ->filter(fn ($entry) => $eventDate === null || (string) ($entry['date'] ?? '') <= $eventDate)
            ->sortByDesc(fn ($entry) => (string) ($entry['date'] ?? ''))
            ->first();

        $boutEntry = collect((array) ($fighter->boxing_bout_count_entries ?? []))
            ->filter(fn ($entry) => is_array($entry) && trim((string) ($entry['date'] ?? '')) !== '')
            ->filter(fn ($entry) => $eventDate === null || (string) ($entry['date'] ?? '') <= $eventDate)
            ->sortByDesc(fn ($entry) => (string) ($entry['date'] ?? ''))
            ->first();

        $wins = is_array($boutEntry) && is_numeric($boutEntry['wins'] ?? null) ? (int) $boutEntry['wins'] : 0;
        $losses = is_array($boutEntry) && is_numeric($boutEntry['losses'] ?? null) ? (int) $boutEntry['losses'] : 0;
        $draws = is_array($boutEntry) && is_numeric($boutEntry['draws'] ?? null) ? (int) $boutEntry['draws'] : 0;
        $total = $wins + $losses + $draws;
        $weight = is_array($weightEntry) && is_numeric($weightEntry['weight_kg'] ?? null) ? (float) $weightEntry['weight_kg'] : null;

        [$ageClass, $performanceClass, $weightClass, $eligible] = $this->calculateBoxingClasses($fighter, $event, $boxingPackage, $wins, $weight, $eventDate);

        $summary = 'Gewicht: ' . ($weight !== null ? $weight . ' kg' : '-')
            . ', Bilanz G/S/N/U: ' . $total . '/' . $wins . '/' . $losses . '/' . $draws;

        return [
            'event_date' => $eventDate,
            'weight' => [
                'date' => is_array($weightEntry) ? ($weightEntry['date'] ?? null) : null,
                'weight_kg' => $weight,
            ],
            'record' => [
                'date' => is_array($boutEntry) ? ($boutEntry['date'] ?? null) : null,
                'total' => $total,
                'wins' => $wins,
                'losses' => $losses,
                'draws' => $draws,
            ],
            'classes' => [
                'age' => $ageClass,
                'performance' => $performanceClass,
                'weight' => $weightClass,
            ],
            'eligible' => $eligible,
            'summary' => $summary,
        ];
    }

    private function calculateBoxingClasses(Fighter $fighter, Event $event, array $boxingPackage, int $wins, ?float $weight, ?string $eventDate): array
    {
        if ((string) ($event->sport_module ?? '') !== 'boxing') {
            return [null, null, null, true];
        }

        $fighterSex = trim((string) ($fighter->sex ?? ''));
        if (! in_array($fighterSex, ['m', 'w'], true)) {
            return [null, null, null, false];
        }

        $ageClasses = (array) ($boxingPackage['age_classes'] ?? []);
        $performanceClasses = (array) ($boxingPackage['performance_classes'] ?? []);
        if (count($ageClasses) === 0 || count($performanceClasses) === 0) {
            return [null, null, null, false];
        }

        $eventAllowedAgeCodes = (array) ($event->boxing_age_classes ?? []);
        if (count($eventAllowedAgeCodes) === 0) {
            $eventAllowedAgeCodes = array_keys($ageClasses);
        }

        $ageYears = null;
        if ($eventDate !== null && $fighter->birth_date) {
            $ageYears = $fighter->birth_date->diffInYears($eventDate);
        }

        $ageCandidates = collect($ageClasses)
            ->filter(fn ($data, $code) => in_array((string) $code, $eventAllowedAgeCodes, true))
            ->map(function ($data, $code) {
                return [
                    'code' => (string) $code,
                    'name' => (string) ($data['name'] ?? $code),
                    'alter' => is_numeric($data['alter'] ?? null) ? (int) $data['alter'] : null,
                    'sex' => trim((string) ($data['sex'] ?? '')),
                    'weights' => (array) ($data['gewicht'] ?? []),
                ];
            })
            ->filter(fn ($candidate) => ($candidate['sex'] ?? '') === '' || ($candidate['sex'] ?? '') === $fighterSex)
            ->values();

        $ageClass = null;
        if ($ageYears !== null) {
            $ageClass = $ageCandidates
                ->filter(fn ($candidate) => $candidate['alter'] !== null && $ageYears <= $candidate['alter'])
                ->sortBy('alter')
                ->first();
        }
        if (! $ageClass) {
            $ageClass = $ageCandidates->sortBy('alter')->first();
        }
        if (! $ageClass) {
            return [null, null, null, false];
        }

        $eventAllowedPerformance = (array) ($event->boxing_performance_classes ?? []);
        if (count($eventAllowedPerformance) === 0) {
            $eventAllowedPerformance = collect($performanceClasses)
                ->map(fn ($class) => (string) ($class['key'] ?? ''))
                ->filter(fn ($key) => $key !== '')
                ->values()
                ->all();
        }

        $performanceClass = collect($performanceClasses)
            ->filter(fn ($class) => in_array((string) ($class['key'] ?? ''), $eventAllowedPerformance, true))
            ->first(function ($class) use ($wins): bool {
                $min = is_numeric($class['wins_min'] ?? null) ? (int) $class['wins_min'] : null;
                $max = is_numeric($class['wins_max'] ?? null) ? (int) $class['wins_max'] : null;

                if ($min !== null && $wins < $min) {
                    return false;
                }
                if ($max !== null && $wins > $max) {
                    return false;
                }

                return true;
            });

        $weightClass = null;
        $weightCandidates = collect((array) ($ageClass['weights'] ?? []))
            ->map(function ($class, $limit) {
                return [
                    'limit' => is_numeric($limit) ? (float) $limit : null,
                    'name' => (string) ($class['name'] ?? $limit),
                    'short' => (string) ($class['short'] ?? $limit),
                ];
            })
            ->filter(fn ($class) => $class['limit'] !== null)
            ->sortBy('limit')
            ->values();

        if ($weight !== null && $weightCandidates->count() > 0) {
            $weightClass = $weightCandidates->first(fn ($class) => $weight <= $class['limit']) ?: $weightCandidates->last();
        }

        $ageLabel = $ageClass['code'] . ' - ' . $ageClass['name'];
        $performanceLabel = is_array($performanceClass)
            ? ((string) ($performanceClass['key'] ?? '') . ' - ' . (string) ($performanceClass['name'] ?? ''))
            : null;
        $weightLabel = is_array($weightClass)
            ? ((string) ($weightClass['short'] ?? '') . ' - ' . (string) ($weightClass['name'] ?? ''))
            : null;

        $eligible = $performanceLabel !== null && $weightLabel !== null;

        return [$ageLabel, $performanceLabel, $weightLabel, $eligible];
    }

    private function isEventEnded(Event $event, $referenceTime): bool
    {
        if ($event->ends_at !== null) {
            return $referenceTime->greaterThan($event->ends_at);
        }

        return $referenceTime->greaterThan($event->starts_at);
    }

    private function resolvePublicStoragePath(string $documentUrl): ?string
    {
        $documentUrl = trim($documentUrl);
        if ($documentUrl === '') {
            return null;
        }

        $path = parse_url($documentUrl, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            $path = $documentUrl;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return $path !== '' ? $path : null;
    }

    private function manageableAthleteClubIds($user): array
    {
        return ClubMembership::query()
            ->where('user_id', $user->getKey())
            ->whereHas('roles', fn ($q) => $q->whereIn('role', [
                ClubMembershipRole::ROLE_CLUB_MANAGER,
                ClubMembershipRole::ROLE_TRAINER,
            ]))
            ->pluck('club_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function canManageEventRegistrations($user, Event $event): bool
    {
        return $user->isPlatformAdmin() || $this->clubPermissions->canManageEvents($user, (int) $event->organizer_club_id);
    }
}
