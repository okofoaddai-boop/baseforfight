<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\Registration;
use App\Services\ClubPermissionService;
use App\Services\RegistrationWorkflowService;
use App\Services\Modules\AiEventExtractionService;
use App\Services\Modules\AiSettingsStore;
use App\Services\Modules\BoxingSettingsStore;
use App\Services\Modules\ModuleManager;
use App\Services\Modules\PdfTextExtractor;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class ClubPortalController extends Controller
{
    public function __construct(
        private readonly ModuleManager $moduleManager,
        private readonly BoxingSettingsStore $boxingSettingsStore,
        private readonly AiSettingsStore $aiSettingsStore,
        private readonly PdfTextExtractor $pdfTextExtractor,
        private readonly AiEventExtractionService $aiEventExtractionService,
        private readonly ClubPermissionService $clubPermissions,
        private readonly RegistrationWorkflowService $registrationWorkflow,
    ) {
    }

    public function show(Request $request, Club $club): View
    {
        $this->authorize('view', $club);

        $members = ClubMembership::query()
            ->with(['user', 'roles'])
            ->where('club_id', $club->getKey())
            ->get()
            ->map(function (ClubMembership $membership) {
                $roleNames = $membership->roles->pluck('role')->all();
                $roleLabels = [
                    ClubMembershipRole::ROLE_CLUB_MANAGER => __('Club-Manager'),
                    ClubMembershipRole::ROLE_EVENT_MANAGER => __('Veranstaltungsmanager'),
                    ClubMembershipRole::ROLE_TRAINER => __('Trainer'),
                ];
                $obj = new \stdClass();
                $obj->id        = $membership->user->getKey();
                $obj->name      = $membership->user->name;
                $obj->email     = $membership->user->email;
                $obj->roles     = $roleNames;
                $obj->role      = implode(' / ', array_map(
                    static fn (string $role): string => $roleLabels[$role] ?? $role,
                    $roleNames
                ));
                $obj->joined_at = $membership->joined_at;
                return $obj;
            })
            ->sortBy('name')
            ->values();

        $fighters = $club->fighters()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $trainers = $members->filter(fn ($member) => in_array(ClubMembershipRole::ROLE_TRAINER, $member->roles, true));
        $canManageClub = $this->canManageClub($request, $club);
        $canManageAthletes = $this->canManageAthletes($request, $club);
        $canManageEvents = $this->canManageEvents($request, $club);
        $currentUserRoles = $request->user()?->clubRolesFor((int) $club->getKey()) ?? [];
        $roleLabels = [
            ClubMembershipRole::ROLE_CLUB_MANAGER => 'Club-Manager',
            ClubMembershipRole::ROLE_EVENT_MANAGER => 'Veranstaltungsmanager',
            ClubMembershipRole::ROLE_TRAINER => 'Trainer',
        ];
        $roleSummary = collect($currentUserRoles)
            ->map(fn (string $role): string => $roleLabels[$role] ?? $role)
            ->implode(' / ');
        $clubEvents = $club->organizedEvents()
            ->withCount([
                'registrations as registered_fighters_count' => fn ($query) => $query->where('status', \App\Models\Registration::STATUS_ACTIVE),
            ])
            ->orderByDesc('starts_at')
            ->get();
        $activeSportModules = $this->activeSportModules();
        $boxingPackages = $this->moduleManager->isActive('boxing')
            ? $this->boxingSettingsStore->enrichPackagesForEventUi($this->boxingSettingsStore->readAllPackages())
            : [];
        $boxingActivePackage = $this->moduleManager->isActive('boxing') ? $this->boxingSettingsStore->readActivePackage() : '';
        $activeBoxingPackage = is_string($boxingActivePackage) && array_key_exists($boxingActivePackage, $boxingPackages)
            ? (array) $boxingPackages[$boxingActivePackage]
            : (array) (count($boxingPackages) > 0 ? reset($boxingPackages) : []);
        $boxingPassKeywords = array_values(array_filter(
            (array) ($activeBoxingPackage['pass_keywords'] ?? ['Arzt gültig bis', 'KO-Sperre gültig bis', 'Registrierung gültig bis']),
            fn ($keyword) => is_string($keyword) && trim($keyword) !== ''
        ));
        $isAiModuleReady = $this->moduleManager->isActive('ai') && $this->aiSettingsStore->isConfigured();

        $activeTab = $request->string('tab', 'overview')->toString();
        if (! in_array($activeTab, ['overview', 'fighters', 'trainers', 'club-data', 'billing', 'events'], true)) {
            $activeTab = 'overview';
        }

        return view('clubs.show', [
            'club' => $club,
            'members' => $members,
            'fighters' => $fighters,
            'trainers' => $trainers,
            'clubEvents' => $clubEvents,
            'canManageClub' => $canManageClub,
            'canManageAthletes' => $canManageAthletes,
            'canManageEvents' => $canManageEvents,
            'roleSummary' => $roleSummary !== '' ? $roleSummary : 'Mitglied',
            'activeTab' => $activeTab,
            'activeSportModules' => $activeSportModules,
            'boxingPackages' => $boxingPackages,
            'boxingActivePackage' => $boxingActivePackage,
            'boxingPassKeywords' => $boxingPassKeywords,
            'isAiModuleReady' => $isAiModuleReady,
        ]);
    }

    public function aiExtractEventFromPdf(Request $request, Club $club): RedirectResponse
    {
        $this->authorizeEventManager($request, $club);

        if (! $this->moduleManager->isActive('ai') || ! $this->aiSettingsStore->isConfigured()) {
            return redirect()
                ->route('clubs.show', ['club' => $club->slug, 'tab' => 'events'])
                ->withErrors(['KI-Modul ist noch nicht aktiv und vollstaendig konfiguriert.']);
        }

        $request->validate([
            'event_pdf' => ['required', 'file', 'mimetypes:application/pdf', 'max:10240'],
        ]);

        $pdf = $request->file('event_pdf');
        $originalName = (string) ($pdf?->getClientOriginalName() ?? 'event.pdf');
        $storedPath = $pdf?->store('event-originals/pending/' . $club->getKey(), 'public');
        $activeModuleSlugs = $this->activeSportModuleSlugs();
        $prefillSportModule = in_array('boxing', $activeModuleSlugs, true)
            ? 'boxing'
            : ($activeModuleSlugs[0] ?? '');

        $prefillTitle = trim((string) pathinfo($originalName, PATHINFO_FILENAME));
        if ($prefillTitle === '') {
            $prefillTitle = 'Neue Veranstaltung';
        }

        $prefill = [
            'sport_module' => $prefillSportModule,
            'title' => $prefillTitle,
            'description' => 'Automatisch aus PDF vorbereitet: ' . $originalName,
        ];

        try {
            if (is_string($storedPath) && $storedPath !== '') {
                $absolutePath = Storage::disk('public')->path($storedPath);
                $pdfText = $this->pdfTextExtractor->extract($absolutePath);
                $extracted = $this->aiEventExtractionService->extractEventData($pdfText);
                $prefill = array_merge($prefill, $this->mapExtractedDataToEventPrefill($extracted, $activeModuleSlugs));
            }
        } catch (Throwable $exception) {
            return redirect()
                ->route('clubs.show', ['club' => $club->slug, 'tab' => 'events'])
                ->withInput([
                    'open_create_event_modal' => '1',
                    'sport_module' => $prefill['sport_module'],
                    'title' => $prefill['title'],
                    'description' => $prefill['description'],
                    'ai_original_pdf_path' => is_string($storedPath) ? $storedPath : '',
                ])
                ->withErrors(['KI-Extraktion fehlgeschlagen: ' . $exception->getMessage()]);
        }

        return redirect()
            ->route('clubs.show', ['club' => $club->slug, 'tab' => 'events'])
            ->withInput([
                'open_create_event_modal' => '1',
                'sport_module' => $prefill['sport_module'] ?? $prefillSportModule,
                'title' => (is_string($prefill['title'] ?? null) && trim((string) $prefill['title']) !== '') ? $prefill['title'] : $prefillTitle,
                'description' => (is_string($prefill['description'] ?? null) && trim((string) $prefill['description']) !== '') ? $prefill['description'] : ('Automatisch aus PDF vorbereitet: ' . $originalName),
                'starts_at' => $prefill['starts_at'] ?? null,
                'ends_at' => $prefill['ends_at'] ?? null,
                'registration_deadline' => $prefill['registration_deadline'] ?? null,
                'venue_name' => $prefill['venue_name'] ?? null,
                'location' => $prefill['location'] ?? null,
                'address_line1' => $prefill['address_line1'] ?? null,
                'address_line2' => $prefill['address_line2'] ?? null,
                'postal_code' => $prefill['postal_code'] ?? null,
                'city' => $prefill['city'] ?? null,
                'country' => $prefill['country'] ?? null,
                'entry_fee_cents' => $prefill['entry_fee_cents'] ?? null,
                'currency' => $prefill['currency'] ?? null,
                'max_registrations' => $prefill['max_registrations'] ?? null,
                'allow_waitlist' => $prefill['allow_waitlist'] ?? '0',
                'registration_approval_mode' => $prefill['registration_approval_mode'] ?? 'auto',
                'status' => $prefill['status'] ?? 'draft',
                'boxing_package_key' => $prefill['boxing_package_key'] ?? null,
                'boxing_age_classes' => $prefill['boxing_age_classes'] ?? [],
                'boxing_performance_classes' => $prefill['boxing_performance_classes'] ?? [],
                'boxing_sexes' => $prefill['boxing_sexes'] ?? [],
                'ai_original_pdf_path' => is_string($storedPath) ? $storedPath : '',
            ])
            ->with('status', 'PDF wurde per KI ausgewertet und das Formular vorausgefüllt.');
    }

    public function aiSuggestPairings(Request $request, Club $club, Event $event): RedirectResponse
    {
        $this->authorizeEventManager($request, $club);

        if ((int) ($event->organizer_club_id ?? 0) !== (int) $club->getKey()) {
            abort(404);
        }

        if (! $this->moduleManager->isActive('ai') || ! $this->aiSettingsStore->isConfigured()) {
            return redirect()
                ->route('clubs.show', ['club' => $club->slug, 'tab' => 'events'])
                ->withErrors(['KI-Modul ist noch nicht aktiv und vollstaendig konfiguriert.']);
        }

        return redirect()
            ->route('clubs.show', ['club' => $club->slug, 'tab' => 'events'])
            ->with('status', 'KI-Paarungsvorschlaege sind vorbereitet. Die eigentliche Vorschlagslogik folgt als naechster Schritt.');
    }

    public function eventRegistrations(Request $request, Club $club, Event $event): View
    {
        $this->authorizeEventManager($request, $club);

        if ((int) $event->organizer_club_id !== (int) $club->getKey()) {
            abort(404);
        }

        $this->registrationWorkflow->lockBillingForEvent($event);
        $event->refresh();

        $registrationData = $this->buildEventRegistrationPanelData($request, $event);

        return view('clubs.partials.event-registrations-panel', [
            'club' => $club,
            'event' => $event,
            'filters' => $registrationData['filters'],
            'groupedRegistrations' => $registrationData['groupedRegistrations'],
            'registrationStats' => $registrationData['registrationStats'],
            'filteredCount' => $registrationData['filteredCount'],
            'totalCount' => $registrationData['totalCount'],
        ]);
    }

    public function manageEventRegistrations(Request $request, Club $club, Event $event): RedirectResponse|JsonResponse
    {
        $this->authorizeEventManager($request, $club);

        if ((int) $event->organizer_club_id !== (int) $club->getKey()) {
            abort(404);
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
            'registration_status_filter' => ['nullable', 'string'],
            'registration_q' => ['nullable', 'string', 'max:120'],
            'registration_group' => ['nullable', 'string'],
            'registration_sort' => ['nullable', 'string'],
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
                $request->user(),
                $validated['reason'] ?? 'club_portal_organizer_status_change',
                ['source' => 'club_portal_modal']
            );

            if ($before !== (string) $validated['status']) {
                $changed++;
            }
        }

        $statusMessage = $changed > 0 ? $changed . ' Meldungen wurden aktualisiert.' : 'Keine Statusänderung erforderlich.';

        if ($request->ajax()) {
            return response()->json([
                'message' => $statusMessage,
            ]);
        }

        return redirect()
            ->route('clubs.show', array_filter([
                'club' => $club->slug,
                'tab' => 'events',
                'open_event' => $event->getKey(),
                'event_modal_tab' => 'registrations',
                'registration_status' => $this->normalizeRegistrationStatusFilter($validated['registration_status_filter'] ?? null),
                'registration_q' => trim((string) ($validated['registration_q'] ?? '')),
                'registration_group' => $this->normalizeRegistrationGroup($validated['registration_group'] ?? null),
                'registration_sort' => $this->normalizeRegistrationSort($validated['registration_sort'] ?? null),
            ], fn ($value) => $value !== null && $value !== ''))
            ->with('status', $statusMessage);
    }

    public function storeFighter(Request $request, Club $club): RedirectResponse
    {
        $this->authorizeAthleteManager($request, $club);

        $activeModuleSlugs = $this->activeSportModuleSlugs();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'sex' => ['required', Rule::in(['m', 'w'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'sport_modules' => ['nullable', 'array'],
            'sport_modules.*' => ['string', Rule::in($activeModuleSlugs)],
            'boxing_weight_dates' => ['nullable', 'array'],
            'boxing_weight_dates.*' => ['nullable', 'date'],
            'boxing_weight_values' => ['nullable', 'array'],
            'boxing_weight_values.*' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'boxing_bout_dates' => ['nullable', 'array'],
            'boxing_bout_dates.*' => ['nullable', 'date'],
            'boxing_bout_wins' => ['nullable', 'array'],
            'boxing_bout_wins.*' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'boxing_bout_losses' => ['nullable', 'array'],
            'boxing_bout_losses.*' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'boxing_bout_draws' => ['nullable', 'array'],
            'boxing_bout_draws.*' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'boxing_pass_keywords' => ['nullable', 'array'],
            'boxing_pass_keywords.*' => ['nullable', 'string', 'max:120'],
            'boxing_pass_dates' => ['nullable', 'array'],
            'boxing_pass_dates.*' => ['nullable', 'date'],
        ]);

        $selectedModules = array_values(array_unique(array_filter(
            (array) ($validated['sport_modules'] ?? []),
            fn ($module) => is_string($module) && in_array($module, $activeModuleSlugs, true)
        )));
        $hasBoxing = in_array('boxing', $selectedModules, true);

        Fighter::query()->create([
            'club_id' => $club->getKey(),
            'created_by_user_id' => $request->user()?->getKey(),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'birth_date' => $validated['birth_date'] ?? null,
            'sex' => $validated['sex'],
            'status' => $validated['status'],
            'sport_modules' => $selectedModules,
            'boxing_weight_entries' => $hasBoxing
                ? $this->normalizeWeightEntries((array) ($validated['boxing_weight_dates'] ?? []), (array) ($validated['boxing_weight_values'] ?? []))
                : [],
            'boxing_bout_count_entries' => $hasBoxing
                ? $this->normalizeBoutEntries(
                    (array) ($validated['boxing_bout_dates'] ?? []),
                    (array) ($validated['boxing_bout_wins'] ?? []),
                    (array) ($validated['boxing_bout_losses'] ?? []),
                    (array) ($validated['boxing_bout_draws'] ?? [])
                )
                : [],
            'boxing_pass_entries' => $hasBoxing
                ? $this->normalizePassEntries((array) ($validated['boxing_pass_keywords'] ?? []), (array) ($validated['boxing_pass_dates'] ?? []))
                : [],
        ]);

        return redirect()
            ->route('clubs.show', ['club' => $club->slug, 'tab' => 'fighters'])
            ->with('status', 'Kämpfer wurde angelegt.');
    }

    public function updateFighter(Request $request, Club $club, Fighter $fighter): RedirectResponse
    {
        $this->authorizeAthleteManager($request, $club);

        if ((int) $fighter->club_id !== (int) $club->getKey()) {
            abort(404);
        }

        $activeModuleSlugs = $this->activeSportModuleSlugs();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'sex' => ['required', Rule::in(['m', 'w'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'sport_modules' => ['nullable', 'array'],
            'sport_modules.*' => ['string', Rule::in($activeModuleSlugs)],
            'boxing_weight_dates' => ['nullable', 'array'],
            'boxing_weight_dates.*' => ['nullable', 'date'],
            'boxing_weight_values' => ['nullable', 'array'],
            'boxing_weight_values.*' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'boxing_bout_dates' => ['nullable', 'array'],
            'boxing_bout_dates.*' => ['nullable', 'date'],
            'boxing_bout_wins' => ['nullable', 'array'],
            'boxing_bout_wins.*' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'boxing_bout_losses' => ['nullable', 'array'],
            'boxing_bout_losses.*' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'boxing_bout_draws' => ['nullable', 'array'],
            'boxing_bout_draws.*' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'boxing_pass_keywords' => ['nullable', 'array'],
            'boxing_pass_keywords.*' => ['nullable', 'string', 'max:120'],
            'boxing_pass_dates' => ['nullable', 'array'],
            'boxing_pass_dates.*' => ['nullable', 'date'],
        ]);

        $selectedModules = array_values(array_unique(array_filter(
            (array) ($validated['sport_modules'] ?? []),
            fn ($module) => is_string($module) && in_array($module, $activeModuleSlugs, true)
        )));
        $hasBoxing = in_array('boxing', $selectedModules, true);

        $fighter->fill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'birth_date' => $validated['birth_date'] ?? null,
            'sex' => $validated['sex'],
            'status' => $validated['status'],
            'sport_modules' => $selectedModules,
            'boxing_weight_entries' => $hasBoxing
                ? $this->normalizeWeightEntries((array) ($validated['boxing_weight_dates'] ?? []), (array) ($validated['boxing_weight_values'] ?? []))
                : [],
            'boxing_bout_count_entries' => $hasBoxing
                ? $this->normalizeBoutEntries(
                    (array) ($validated['boxing_bout_dates'] ?? []),
                    (array) ($validated['boxing_bout_wins'] ?? []),
                    (array) ($validated['boxing_bout_losses'] ?? []),
                    (array) ($validated['boxing_bout_draws'] ?? [])
                )
                : [],
            'boxing_pass_entries' => $hasBoxing
                ? $this->normalizePassEntries((array) ($validated['boxing_pass_keywords'] ?? []), (array) ($validated['boxing_pass_dates'] ?? []))
                : [],
        ]);
        $fighter->save();

        $this->refreshOpenRegistrationSnapshots($fighter, $request->user()?->getKey());

        $returnEventId = $request->integer('return_event_id');
        if ($returnEventId > 0) {
            return redirect()
                ->route('events.show', $returnEventId)
                ->with('status', 'Kämpfer aktualisiert. Meldung wurde sofort neu berechnet.');
        }

        return redirect()
            ->route('clubs.show', ['club' => $club->slug, 'tab' => 'fighters'])
            ->with('status', 'Kämpfer wurde aktualisiert.');
    }

    public function update(Request $request, Club $club): RedirectResponse
    {
        $this->authorizeManager($request, $club);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('clubs', 'slug')->ignore($club->getKey())],
            'description' => ['nullable', 'string', 'max:3000'],
            'billing_company_name' => ['nullable', 'string', 'max:255'],
            'billing_contact_name' => ['nullable', 'string', 'max:255'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'billing_address_line1' => ['nullable', 'string', 'max:255'],
            'billing_address_line2' => ['nullable', 'string', 'max:255'],
            'billing_zip' => ['nullable', 'string', 'max:50'],
            'billing_city' => ['nullable', 'string', 'max:255'],
            'billing_country' => ['nullable', 'string', 'max:2'],
            'tab' => ['nullable', 'string'],
        ]);

        $club->fill([
            'name' => $validated['name'],
            'slug' => strtolower(trim($validated['slug'])),
            'description' => $validated['description'] ?? null,
            'billing_company_name' => $validated['billing_company_name'] ?? null,
            'billing_contact_name' => $validated['billing_contact_name'] ?? null,
            'billing_email' => $validated['billing_email'] ?? null,
            'billing_address_line1' => $validated['billing_address_line1'] ?? null,
            'billing_address_line2' => $validated['billing_address_line2'] ?? null,
            'billing_zip' => $validated['billing_zip'] ?? null,
            'billing_city' => $validated['billing_city'] ?? null,
            'billing_country' => strtoupper((string) ($validated['billing_country'] ?? 'DE')),
        ]);
        $club->save();

        $tab = $validated['tab'] ?? 'club-data';

        return redirect()
            ->route('clubs.show', ['club' => $club->slug, 'tab' => $tab])
            ->with('status', 'Vereinsdaten wurden gespeichert.');
    }

    public function storeEvent(Request $request, Club $club): RedirectResponse
    {
        $this->authorizeEventManager($request, $club);

        $activeModuleSlugs = $this->activeSportModuleSlugs();

        $validated = $request->validate([
            'sport_module' => ['required', Rule::in($activeModuleSlugs)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'event_original_pdf' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:10240'],
            'ai_original_pdf_path' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'registration_deadline' => ['nullable', 'date', 'before_or_equal:starts_at'],
            'registration_approval_mode' => ['required', Rule::in(['auto', 'manual'])],
            'max_registrations' => ['nullable', 'integer', 'min:1'],
            'allow_waitlist' => ['nullable', 'boolean'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'boxing_package_key' => ['nullable', 'string', 'max:80'],
            'boxing_age_classes' => ['nullable', 'array'],
            'boxing_age_classes.*' => ['nullable', 'string', 'max:80'],
            'boxing_sexes' => ['nullable', 'array'],
            'boxing_sexes.*' => ['nullable', 'in:m,w'],
            'boxing_performance_classes' => ['nullable', 'array'],
            'boxing_performance_classes.*' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(['draft', 'published', 'cancelled'])],
            'entry_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'entry_fee_cents' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $entryFeeCents = $this->resolveEntryFeeCents($validated);

        $infoDocuments = [];

        if ($request->hasFile('event_original_pdf')) {
            $uploadedPath = $request->file('event_original_pdf')?->store('event-originals/' . $club->getKey(), 'public');
            if (is_string($uploadedPath) && $uploadedPath !== '') {
                $infoDocuments[] = Storage::disk('public')->url($uploadedPath);
            }
        }

        $aiPdfPath = trim((string) ($validated['ai_original_pdf_path'] ?? ''));
        if ($aiPdfPath !== '' && Storage::disk('public')->exists($aiPdfPath)) {
            $infoDocuments[] = Storage::disk('public')->url($aiPdfPath);
        }

        $normalizedBoxingSelections = $this->normalizeBoxingEventSelections(
            (string) ($validated['sport_module'] ?? ''),
            isset($validated['boxing_package_key']) ? (string) $validated['boxing_package_key'] : '',
            (array) ($validated['boxing_age_classes'] ?? []),
            (array) ($validated['boxing_sexes'] ?? []),
            (array) ($validated['boxing_performance_classes'] ?? [])
        );

        Event::query()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'registration_deadline' => $validated['registration_deadline'] ?? null,
            'registration_approval_mode' => $validated['registration_approval_mode'],
            'max_registrations' => $validated['max_registrations'] ?? null,
            'allow_waitlist' => (bool) ($validated['allow_waitlist'] ?? false),
            'venue_name' => $validated['venue_name'] ?? null,
            'location' => $validated['location'] ?? null,
            'sport_module' => $validated['sport_module'],
            'address_line1' => $validated['address_line1'] ?? null,
            'address_line2' => $validated['address_line2'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => strtoupper((string) ($validated['country'] ?? 'DE')),
            'boxing_package_key' => $validated['boxing_package_key'] ?? null,
            'boxing_age_classes' => $normalizedBoxingSelections['age_classes'],
            'boxing_sexes' => $normalizedBoxingSelections['sexes'],
            'boxing_performance_classes' => $normalizedBoxingSelections['performance_classes'],
            'status' => $validated['status'],
            'entry_fee_cents' => $entryFeeCents,
            'currency' => strtoupper((string) ($validated['currency'] ?? 'EUR')),
            'info_documents' => $infoDocuments,
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'organizer_club_id' => $club->getKey(),
            'created_by_user_id' => $request->user()?->getKey(),
        ]);

        return redirect()
            ->route('clubs.show', ['club' => $club->slug, 'tab' => 'events'])
            ->with('status', 'Veranstaltung wurde angelegt.');
    }

    public function updateEvent(Request $request, Club $club, Event $event): RedirectResponse
    {
        $this->authorizeEventManager($request, $club);

        if ((int) $event->organizer_club_id !== (int) $club->getKey()) {
            abort(404);
        }

        $activeModuleSlugs = $this->activeSportModuleSlugs();

        $validated = $request->validate([
            'sport_module' => ['required', Rule::in($activeModuleSlugs)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'event_original_pdf' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:10240'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'registration_deadline' => ['nullable', 'date', 'before_or_equal:starts_at'],
            'registration_approval_mode' => ['required', Rule::in(['auto', 'manual'])],
            'max_registrations' => ['nullable', 'integer', 'min:1'],
            'allow_waitlist' => ['nullable', 'boolean'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'boxing_package_key' => ['nullable', 'string', 'max:80'],
            'boxing_age_classes' => ['nullable', 'array'],
            'boxing_age_classes.*' => ['nullable', 'string', 'max:80'],
            'boxing_sexes' => ['nullable', 'array'],
            'boxing_sexes.*' => ['nullable', 'in:m,w'],
            'boxing_performance_classes' => ['nullable', 'array'],
            'boxing_performance_classes.*' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(['draft', 'published', 'cancelled'])],
            'entry_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'entry_fee_cents' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $entryFeeCents = $this->resolveEntryFeeCents($validated);

        $infoDocuments = is_array($event->info_documents) ? $event->info_documents : [];
        if ($request->hasFile('event_original_pdf')) {
            $uploadedPath = $request->file('event_original_pdf')?->store('event-originals/' . $club->getKey(), 'public');
            if (is_string($uploadedPath) && $uploadedPath !== '') {
                $infoDocuments[] = Storage::disk('public')->url($uploadedPath);
            }
        }

        $normalizedBoxingSelections = $this->normalizeBoxingEventSelections(
            (string) ($validated['sport_module'] ?? ''),
            isset($validated['boxing_package_key']) ? (string) $validated['boxing_package_key'] : '',
            (array) ($validated['boxing_age_classes'] ?? []),
            (array) ($validated['boxing_sexes'] ?? []),
            (array) ($validated['boxing_performance_classes'] ?? [])
        );

        $event->fill([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'registration_deadline' => $validated['registration_deadline'] ?? null,
            'registration_approval_mode' => $validated['registration_approval_mode'],
            'max_registrations' => $validated['max_registrations'] ?? null,
            'allow_waitlist' => (bool) ($validated['allow_waitlist'] ?? false),
            'venue_name' => $validated['venue_name'] ?? null,
            'location' => $validated['location'] ?? null,
            'sport_module' => $validated['sport_module'],
            'address_line1' => $validated['address_line1'] ?? null,
            'address_line2' => $validated['address_line2'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => strtoupper((string) ($validated['country'] ?? 'DE')),
            'boxing_package_key' => $validated['boxing_package_key'] ?? null,
            'boxing_age_classes' => $normalizedBoxingSelections['age_classes'],
            'boxing_sexes' => $normalizedBoxingSelections['sexes'],
            'boxing_performance_classes' => $normalizedBoxingSelections['performance_classes'],
            'status' => $validated['status'],
            'entry_fee_cents' => $entryFeeCents,
            'currency' => strtoupper((string) ($validated['currency'] ?? 'EUR')),
            'info_documents' => array_values(array_unique(array_filter($infoDocuments, fn ($doc) => is_string($doc) && trim($doc) !== ''))),
            'published_at' => $validated['status'] === 'published' ? ($event->published_at ?? now()) : null,
        ]);
        $event->save();

        return redirect()
            ->route('clubs.show', ['club' => $club->slug, 'tab' => 'events'])
            ->with('status', 'Veranstaltung wurde aktualisiert.');
    }

    private function authorizeManager(Request $request, Club $club): void
    {
        if (! $this->canManageClub($request, $club)) {
            abort(403);
        }
    }

    private function authorizeAthleteManager(Request $request, Club $club): void
    {
        if (! $this->canManageAthletes($request, $club)) {
            abort(403);
        }
    }

    private function authorizeEventManager(Request $request, Club $club): void
    {
        if (! $this->canManageEvents($request, $club)) {
            abort(403);
        }
    }

    private function canManageClub(Request $request, Club $club): bool
    {
        $user = $request->user();
        if ($user === null) {
            return false;
        }

        return $this->clubPermissions->canManageClub($user, (int) $club->getKey());
    }

    private function canManageAthletes(Request $request, Club $club): bool
    {
        $user = $request->user();
        if ($user === null) {
            return false;
        }

        return $this->clubPermissions->canManageAthletes($user, (int) $club->getKey());
    }

    private function canManageEvents(Request $request, Club $club): bool
    {
        $user = $request->user();
        if ($user === null) {
            return false;
        }

        return $this->clubPermissions->canManageEvents($user, (int) $club->getKey());
    }

    private function buildEventRegistrationPanelData(Request $request, Event $event): array
    {
        $filters = [
            'status' => $this->normalizeRegistrationStatusFilter($request->query('registration_status')),
            'query' => trim($request->string('registration_q', '')->toString()),
            'group' => $this->normalizeRegistrationGroup($request->query('registration_group')),
            'sort' => $this->normalizeRegistrationSort($request->query('registration_sort')),
        ];

        $registrations = Registration::query()
            ->with(['fighter.club', 'registeredBy'])
            ->where('event_id', $event->getKey())
            ->get();

        $registrationStats = [
            'active' => $registrations->where('status', Registration::STATUS_ACTIVE)->count(),
            'waiting' => $registrations->where('status', Registration::STATUS_WAITING)->count(),
            'withdrawn' => $registrations->where('status', Registration::STATUS_WITHDRAWN)->count(),
            'billable' => $registrations->filter(fn (Registration $registration) => $registration->billable_at !== null)->count(),
        ];

        $filtered = $registrations
            ->filter(function (Registration $registration) use ($filters): bool {
                if ($filters['status'] !== 'all' && (string) $registration->status !== $filters['status']) {
                    return false;
                }

                if ($filters['query'] === '') {
                    return true;
                }

                $needle = mb_strtolower($filters['query']);
                $haystack = mb_strtolower(implode(' ', array_filter([
                    trim((string) (($registration->fighter?->first_name ?? '') . ' ' . ($registration->fighter?->last_name ?? ''))),
                    (string) ($registration->fighter?->club?->name ?? ''),
                    (string) ($registration->registeredBy?->name ?? ''),
                    (string) ($registration->registeredBy?->email ?? ''),
                ], fn ($value) => trim((string) $value) !== '')));

                return str_contains($haystack, $needle);
            })
            ->sortBy([
                fn (Registration $registration) => $filters['group'] === 'weight'
                    ? $this->registrationWeightSortValue($registration)
                    : mb_strtolower($this->registrationClubName($registration)),
                fn (Registration $registration) => match ($filters['sort']) {
                    'club' => mb_strtolower($this->registrationClubName($registration)),
                    'fighter' => mb_strtolower($this->registrationFighterName($registration)),
                    'changed_at' => $registration->status_changed_at?->timestamp ?? $registration->updated_at?->timestamp ?? 0,
                    default => $this->registrationWeightSortValue($registration),
                },
                fn (Registration $registration) => mb_strtolower($this->registrationFighterName($registration)),
            ])
            ->values();

        $groupedRegistrations = $filtered
            ->groupBy(fn (Registration $registration) => $this->registrationGroupLabel($registration, $filters['group']));

        return [
            'filters' => $filters,
            'groupedRegistrations' => $groupedRegistrations,
            'registrationStats' => $registrationStats,
            'filteredCount' => $filtered->count(),
            'totalCount' => $registrations->count(),
        ];
    }

    private function normalizeRegistrationStatusFilter(mixed $value): string
    {
        $normalized = trim((string) $value);

        return in_array($normalized, ['all', Registration::STATUS_ACTIVE, Registration::STATUS_WAITING, Registration::STATUS_WITHDRAWN], true)
            ? $normalized
            : 'all';
    }

    private function normalizeRegistrationGroup(mixed $value): string
    {
        $normalized = trim((string) $value);

        return in_array($normalized, ['club', 'weight'], true) ? $normalized : 'club';
    }

    private function normalizeRegistrationSort(mixed $value): string
    {
        $normalized = trim((string) $value);

        return in_array($normalized, ['weight_class', 'club', 'fighter', 'changed_at'], true) ? $normalized : 'weight_class';
    }

    private function registrationGroupLabel(Registration $registration, string $group): string
    {
        if ($group === 'weight') {
            $weightClass = trim((string) data_get($registration->fighter_snapshot, 'classes.weight', ''));
            return $weightClass !== '' ? $weightClass : 'Ohne Gewichtsklasse';
        }

        return $this->registrationClubName($registration);
    }

    private function registrationWeightSortValue(Registration $registration): float
    {
        $weight = data_get($registration->fighter_snapshot, 'weight.weight_kg');

        return is_numeric($weight) ? (float) $weight : 9999.0;
    }

    private function registrationClubName(Registration $registration): string
    {
        return trim((string) ($registration->fighter?->club?->name ?? 'Ohne Verein'));
    }

    private function registrationFighterName(Registration $registration): string
    {
        return trim((string) (($registration->fighter?->first_name ?? '') . ' ' . ($registration->fighter?->last_name ?? '')));
    }

    private function activeSportModules(): array
    {
        return collect($this->moduleManager->all())
            ->filter(fn (array $module): bool => (bool) ($module['is_active'] ?? false))
            ->filter(fn (array $module): bool => (string) ($module['class'] ?? 'integration') === 'sport')
            ->values()
            ->all();
    }

    private function activeSportModuleSlugs(): array
    {
        return collect($this->activeSportModules())
            ->pluck('slug')
            ->map(fn ($slug) => (string) $slug)
            ->filter(fn (string $slug): bool => $slug !== '')
            ->values()
            ->all();
    }

    private function normalizeWeightEntries(array $dates, array $values): array
    {
        $entries = [];

        foreach ($dates as $index => $date) {
            $normalizedDate = trim((string) $date);
            $valueRaw = $values[$index] ?? null;
            $normalizedValue = is_numeric($valueRaw) ? (float) $valueRaw : null;

            if ($normalizedDate === '' && $normalizedValue === null) {
                continue;
            }

            $entries[] = [
                'date' => $normalizedDate !== '' ? $normalizedDate : null,
                'weight_kg' => $normalizedValue,
            ];
        }

        return $entries;
    }

    private function resolveEntryFeeCents(array $validated): ?int
    {
        if (array_key_exists('entry_fee_amount', $validated) && $validated['entry_fee_amount'] !== null && $validated['entry_fee_amount'] !== '') {
            return (int) round(((float) $validated['entry_fee_amount']) * 100);
        }

        if (array_key_exists('entry_fee_cents', $validated) && is_numeric($validated['entry_fee_cents'])) {
            return (int) $validated['entry_fee_cents'];
        }

        return null;
    }

    private function normalizeBoxingEventSelections(
        string $sportModule,
        string $boxingPackageKey,
        array $ageClasses,
        array $sexes,
        array $performanceClasses
    ): array {
        if ($sportModule !== 'boxing') {
            return [
                'age_classes' => [],
                'sexes' => [],
                'performance_classes' => [],
            ];
        }

        $packages = $this->boxingSettingsStore->readAllPackages();
        $package = array_key_exists($boxingPackageKey, $packages)
            ? (array) $packages[$boxingPackageKey]
            : [];

        $packageAgeClasses = (array) ($package['age_classes'] ?? []);
        $packagePerformanceClasses = (array) ($package['performance_classes'] ?? []);

        $availableSexes = collect($packageAgeClasses)
            ->map(fn ($ageData) => trim((string) (($ageData['sex'] ?? ''))))
            ->filter(fn ($sex) => in_array($sex, ['m', 'w'], true))
            ->unique()
            ->values()
            ->all();

        $normalizedAgeClasses = array_values(array_unique(array_filter(
            $ageClasses,
            function ($ageCode) use ($packageAgeClasses): bool {
                if (! is_string($ageCode) || trim($ageCode) === '') {
                    return false;
                }

                if (! array_key_exists($ageCode, $packageAgeClasses)) {
                    return false;
                }

                return true;
            }
        )));

        $normalizedSexes = collect($normalizedAgeClasses)
            ->map(function ($ageCode) use ($packageAgeClasses): ?string {
                if (! is_string($ageCode) || ! array_key_exists($ageCode, $packageAgeClasses)) {
                    return null;
                }

                $sex = trim((string) (($packageAgeClasses[$ageCode]['sex'] ?? '')));

                return in_array($sex, ['m', 'w'], true) ? $sex : null;
            })
            ->filter(fn ($sex) => is_string($sex))
            ->unique()
            ->values()
            ->all();

        if (count($availableSexes) > 0) {
            $normalizedSexes = array_values(array_intersect($normalizedSexes, $availableSexes));
        }

        $availablePerformanceKeys = collect($packagePerformanceClasses)
            ->map(fn ($class) => (string) ($class['key'] ?? ''))
            ->filter(fn ($key) => $key !== '')
            ->values()
            ->all();

        $normalizedPerformanceClasses = array_values(array_unique(array_filter(
            $performanceClasses,
            fn ($key) => is_string($key) && $key !== '' && (count($availablePerformanceKeys) === 0 || in_array($key, $availablePerformanceKeys, true))
        )));

        return [
            'age_classes' => $normalizedAgeClasses,
            'sexes' => $normalizedSexes,
            'performance_classes' => $normalizedPerformanceClasses,
        ];
    }

    private function normalizeBoutEntries(array $dates, array $wins, array $losses, array $draws): array
    {
        $entries = [];

        foreach ($dates as $index => $date) {
            $normalizedDate = trim((string) $date);
            $winsRaw = $wins[$index] ?? null;
            $lossesRaw = $losses[$index] ?? null;
            $drawsRaw = $draws[$index] ?? null;
            $normalizedWins = is_numeric($winsRaw) ? (int) $winsRaw : null;
            $normalizedLosses = is_numeric($lossesRaw) ? (int) $lossesRaw : null;
            $normalizedDraws = is_numeric($drawsRaw) ? (int) $drawsRaw : null;

            if ($normalizedDate === '' && $normalizedWins === null && $normalizedLosses === null && $normalizedDraws === null) {
                continue;
            }

            $entries[] = [
                'date' => $normalizedDate !== '' ? $normalizedDate : null,
                'wins' => $normalizedWins,
                'losses' => $normalizedLosses,
                'draws' => $normalizedDraws,
            ];
        }

        return $entries;
    }

    private function normalizePassEntries(array $keywords, array $dates): array
    {
        $entries = [];

        foreach ($keywords as $index => $keyword) {
            $normalizedKeyword = trim((string) $keyword);
            $date = trim((string) ($dates[$index] ?? ''));

            if ($normalizedKeyword === '' && $date === '') {
                continue;
            }

            $entries[] = [
                'keyword' => $normalizedKeyword,
                'date' => $date !== '' ? $date : null,
            ];
        }

        return $entries;
    }

    private function mapExtractedDataToEventPrefill(array $extracted, array $activeModuleSlugs): array
    {
        $sportModule = trim((string) ($extracted['sport_module'] ?? ''));
        if ($sportModule === '' || ! in_array($sportModule, $activeModuleSlugs, true)) {
            $sportModule = in_array('boxing', $activeModuleSlugs, true)
                ? 'boxing'
                : ($activeModuleSlugs[0] ?? '');
        }

        $status = trim((string) ($extracted['status'] ?? 'draft'));
        if (! in_array($status, ['draft', 'published', 'cancelled'], true)) {
            $status = 'draft';
        }

        $country = strtoupper(trim((string) ($extracted['country'] ?? 'DE')));
        if (strlen($country) !== 2) {
            $country = 'DE';
        }

        $currency = strtoupper(trim((string) ($extracted['currency'] ?? 'EUR')));
        if (strlen($currency) !== 3) {
            $currency = 'EUR';
        }

        return [
            'sport_module' => $sportModule,
            'title' => trim((string) ($extracted['title'] ?? '')),
            'description' => trim((string) ($extracted['description'] ?? '')),
            'starts_at' => $this->normalizeDateTimeLocal($extracted['starts_at'] ?? null),
            'ends_at' => $this->normalizeDateTimeLocal($extracted['ends_at'] ?? null),
            'registration_deadline' => $this->normalizeDateTimeLocal($extracted['registration_deadline'] ?? null),
            'venue_name' => trim((string) ($extracted['venue_name'] ?? '')),
            'location' => trim((string) ($extracted['location'] ?? '')),
            'address_line1' => trim((string) ($extracted['address_line1'] ?? '')),
            'address_line2' => trim((string) ($extracted['address_line2'] ?? '')),
            'postal_code' => trim((string) ($extracted['postal_code'] ?? '')),
            'city' => trim((string) ($extracted['city'] ?? '')),
            'country' => $country,
            'entry_fee_cents' => is_numeric($extracted['entry_fee_cents'] ?? null) ? (int) $extracted['entry_fee_cents'] : null,
            'currency' => $currency,
            'max_registrations' => is_numeric($extracted['max_registrations'] ?? null) ? (int) $extracted['max_registrations'] : null,
            'allow_waitlist' => (bool) ($extracted['allow_waitlist'] ?? false) ? '1' : '0',
            'registration_approval_mode' => 'auto',
            'status' => $status,
            'boxing_package_key' => trim((string) ($extracted['boxing_package_key'] ?? '')),
            'boxing_age_classes' => array_values(array_filter((array) ($extracted['boxing_age_classes'] ?? []), fn ($value) => is_string($value) && trim($value) !== '')),
            'boxing_performance_classes' => array_values(array_filter((array) ($extracted['boxing_performance_classes'] ?? []), fn ($value) => is_string($value) && trim($value) !== '')),
            'boxing_sexes' => array_values(array_filter((array) ($extracted['boxing_sexes'] ?? []), fn ($value) => is_string($value) && in_array($value, ['m', 'w'], true))),
        ];
    }

    private function normalizeDateTimeLocal(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d\\TH:i');
        } catch (Throwable) {
            return null;
        }
    }

    private function refreshOpenRegistrationSnapshots(Fighter $fighter, ?int $userId = null): void
    {
        $boxingPackages = $this->boxingSettingsStore->readAllPackages();
        $activePackageKey = $this->boxingSettingsStore->readActivePackage();

        Registration::query()
            ->with('event')
            ->where('fighter_id', $fighter->getKey())
            ->get()
            ->each(function (Registration $registration) use ($fighter, $boxingPackages, $activePackageKey, $userId): void {
                $event = $registration->event;
                if (! $event instanceof Event) {
                    return;
                }

                if ($event->registration_deadline !== null && now()->greaterThan($event->registration_deadline)) {
                    return;
                }

                $packageKey = trim((string) ($event->boxing_package_key ?? ''));
                if ($packageKey === '') {
                    $packageKey = $activePackageKey;
                }
                $boxingPackage = array_key_exists($packageKey, $boxingPackages) ? (array) $boxingPackages[$packageKey] : [];

                $snapshot = $this->buildFighterSnapshotForEvent($fighter, $event, $boxingPackage);
                $registration->fill([
                    'fighter_snapshot' => $snapshot,
                    'notes' => $snapshot['summary'] ?? null,
                    'registered_by_user_id' => $userId ?: $registration->registered_by_user_id,
                ]);
                $registration->save();
            });
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

        [$ageClass, $performanceClass, $weightClass] = $this->calculateBoxingClasses($fighter, $event, $boxingPackage, $wins, $weight, $eventDate);

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
            'summary' => $summary,
        ];
    }

    private function calculateBoxingClasses(Fighter $fighter, Event $event, array $boxingPackage, int $wins, ?float $weight, ?string $eventDate): array
    {
        if ((string) ($event->sport_module ?? '') !== 'boxing') {
            return [null, null, null];
        }

        $fighterSex = trim((string) ($fighter->sex ?? ''));
        if (! in_array($fighterSex, ['m', 'w'], true)) {
            return [null, null, null];
        }

        $ageClasses = (array) ($boxingPackage['age_classes'] ?? []);
        $performanceClasses = (array) ($boxingPackage['performance_classes'] ?? []);
        if (count($ageClasses) === 0 || count($performanceClasses) === 0) {
            return [null, null, null];
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
        if (is_array($ageClass) && $weight !== null) {
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

            if ($weightCandidates->count() > 0) {
                $weightClass = $weightCandidates->first(fn ($class) => $weight <= $class['limit']) ?: $weightCandidates->last();
            }
        }

        $ageLabel = is_array($ageClass) ? ($ageClass['code'] . ' - ' . $ageClass['name']) : null;
        $performanceLabel = is_array($performanceClass)
            ? ((string) ($performanceClass['key'] ?? '') . ' - ' . (string) ($performanceClass['name'] ?? ''))
            : null;
        $weightLabel = is_array($weightClass)
            ? ((string) ($weightClass['short'] ?? '') . ' - ' . (string) ($weightClass['name'] ?? ''))
            : null;

        return [$ageLabel, $performanceLabel, $weightLabel];
    }
}
