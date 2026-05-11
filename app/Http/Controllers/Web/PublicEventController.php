<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use App\Services\Modules\BoxingSettingsStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PublicEventController extends Controller
{
    public function __construct(
        private readonly BoxingSettingsStore $boxingSettingsStore,
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
            $userClubs = $user->clubs()
                ->withCount(['fighters', 'users'])
                ->orderBy('name')
                ->get();
        }

        if (! $isPrivileged) {
            $query->where('status', 'published');
        }

        $events = $query->limit(12)->get();

        $now = now();
        $events = $events->map(function (Event $event) use ($now): Event {
            $isEnded = $this->isEventEnded($event, $now);
            $event->setAttribute('display_status', $event->status === 'draft' ? 'draft' : ($isEnded ? 'beendet' : null));

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

        $registrationDeadline = $event->registration_deadline;
        $now = now();
        $isEnded = $this->isEventEnded($event, $now);
        $isRegistrationOpen = $event->status === 'published'
            && $event->cancelled_at === null
            && ! $isEnded
            && ($registrationDeadline === null || now()->lessThanOrEqualTo($registrationDeadline));

        $displayStatus = $event->status === 'draft' ? 'draft' : ($isEnded ? 'beendet' : null);

        $boxingPackages = $this->boxingSettingsStore->readAllPackages();
        $boxingPackageKey = trim((string) ($event->boxing_package_key ?? ''));
        if ($boxingPackageKey === '') {
            $boxingPackageKey = $this->boxingSettingsStore->readActivePackage();
        }
        $boxingPackage = array_key_exists($boxingPackageKey, $boxingPackages)
            ? (array) $boxingPackages[$boxingPackageKey]
            : [];

        $manageableRoles = ['manager', 'owner', 'admin', 'trainer', 'coach'];
        $manageableClubIds = $user
            ? $user->clubs()
                ->wherePivotIn('role', $manageableRoles)
                ->pluck('clubs.id')
                ->map(fn ($id) => (int) $id)
                ->all()
            : [];

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

        $registeredFighterIds = $registrationByFighterId->keys()->map(fn ($id) => (int) $id)->all();
        $registeredFighters = $eligibleFighters
            ->filter(fn (Fighter $fighter) => in_array((int) $fighter->getKey(), $registeredFighterIds, true))
            ->values();
        $possibleFighters = $isRegistrationOpen
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
            'isRegistrationOpen' => $isRegistrationOpen,
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

        if ($event->registration_deadline !== null && now()->greaterThan($event->registration_deadline)) {
            return redirect()
                ->route('events.show', ['event' => $event, 'tab' => 'registrations'])
                ->withErrors(['Der Anmeldeschluss ist bereits abgelaufen.']);
        }

        $manageableClubIds = $user->clubs()
            ->wherePivotIn('role', ['manager', 'owner', 'admin', 'trainer', 'coach'])
            ->pluck('clubs.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($manageableClubIds) === 0) {
            return redirect()
                ->route('events.show', ['event' => $event, 'tab' => 'registrations'])
                ->withErrors(['Keine verwaltbaren Vereine für Meldungen verfügbar.']);
        }

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

        $eligibleRegistrations = Registration::query()
            ->where('event_id', $event->getKey())
            ->whereIn('fighter_id', $eligibleFighterIds)
            ->get()
            ->keyBy('fighter_id');

        $baseCount = Registration::query()
            ->where('event_id', $event->getKey())
            ->where('status', '!=', 'cancelled')
            ->count();

        $newlyRegistered = 0;
        $newlyUnregistered = 0;
        $limitSkipped = 0;

        DB::transaction(function () use (
            $event,
            $selectedFighterIds,
            $eligibleFighterIds,
            $eligibleRegistrations,
            $user,
            $boxingPackage,
            &$baseCount,
            &$newlyRegistered,
            &$newlyUnregistered,
            &$limitSkipped
        ): void {
            $selectedSet = array_fill_keys($selectedFighterIds, true);

            foreach ($eligibleFighterIds as $fighterId) {
                $existing = $eligibleRegistrations->get($fighterId);
                $isSelected = array_key_exists($fighterId, $selectedSet);

                if (! $isSelected) {
                    if ($existing) {
                        if ($existing->status !== 'cancelled') {
                            $baseCount = max(0, $baseCount - 1);
                        }
                        $existing->delete();
                        $newlyUnregistered++;
                    }
                    continue;
                }

                $fighter = Fighter::query()->find($fighterId);
                if (! $fighter) {
                    continue;
                }

                $snapshot = $this->buildFighterSnapshotForEvent($fighter, $event, $boxingPackage);

                if ($existing) {
                    $existing->fill([
                        'fighter_snapshot' => $snapshot,
                        'notes' => $snapshot['summary'] ?? null,
                        'registered_by_user_id' => $user->getKey(),
                    ]);
                    $existing->save();
                    continue;
                }

                $maxRegistrations = $event->max_registrations;
                $limitReached = is_numeric($maxRegistrations) && $baseCount >= (int) $maxRegistrations;

                if ($limitReached && ! $event->allow_waitlist) {
                    $limitSkipped++;
                    continue;
                }

                Registration::query()->create([
                    'fighter_id' => $fighterId,
                    'event_id' => $event->getKey(),
                    'status' => $limitReached ? 'waitlisted' : 'pending',
                    'registered_by_user_id' => $user->getKey(),
                    'fighter_snapshot' => $snapshot,
                    'notes' => $snapshot['summary'] ?? null,
                ]);

                $newlyRegistered++;
                if (! $limitReached) {
                    $baseCount++;
                }
            }
        });

        $statusParts = [];
        if ($newlyRegistered > 0) {
            $statusParts[] = $newlyRegistered . ' gemeldet';
        }
        if ($newlyUnregistered > 0) {
            $statusParts[] = $newlyUnregistered . ' abgemeldet';
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
}
