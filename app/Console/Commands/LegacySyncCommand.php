<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\ClubMembershipRole;
use App\Models\Event;
use App\Models\Registration;
use App\Services\ClubPermissionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class LegacySyncCommand extends Command
{
    protected $signature = 'baseforfight:legacy-sync
        {--dry-run : Validate and simulate without writing changes}
        {--limit-users=0 : Optional cap for processed legacy users (descending user_id)}';

    protected $description = 'Synchronize legacy BaseForFight data into the new schema (baseline + delta).';

    private Connection $legacy;

    private Connection $target;

    private bool $dryRun = false;

    private int $runId = 0;

    /**
     * @var array<string, int>
     */
    private array $counts = [
        'users_created' => 0,
        'users_updated' => 0,
        'users_ignored' => 0,
        'clubs_created' => 0,
        'clubs_reused' => 0,
        'memberships_created' => 0,
        'fighters_created' => 0,
        'fighters_updated' => 0,
        'fighters_ignored' => 0,
        'events_created' => 0,
        'events_updated' => 0,
        'events_ignored' => 0,
        'registrations_created' => 0,
        'registrations_updated' => 0,
        'registrations_ignored' => 0,
    ];

    /**
     * @var array<int, int>
     */
    private array $legacyUserToNewUser = [];

    /**
     * @var array<int, int>
     */
    private array $legacyUserToClub = [];

    /**
     * @var array<int, int>
     */
    private array $legacyFighterToNew = [];

    /**
     * @var array<int, int>
     */
    private array $legacyEventToNew = [];

    /**
     * @var array<string, int>
     */
    private array $clubByExactName = [];

    /**
     * @var array<int, object>
     */
    private array $legacyUserData = [];

    /**
     * @var array<string, object>
     */
    private array $legacyClubDirectory = [];

    /**
     * @var array<int, array<int, string>>
     */
    private array $legacyEventDocs = [];

    public function handle(ClubPermissionService $clubPermissionService): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $limitUsers = max(0, (int) $this->option('limit-users'));

        $this->legacy = DB::connection('legacy');
        $this->target = DB::connection();

        $this->line($this->dryRun ? 'Legacy sync dry-run started.' : 'Legacy sync started.');

        try {
            $this->prepareLegacyLookups();

            if (! $this->dryRun) {
                $this->runId = (int) $this->legacy->table('bff_sync_runs')->insertGetId([
                    'run_type' => 'migration',
                    'started_at' => now(),
                    'success' => 0,
                ]);
            }

            $this->syncUsersAndClubs($clubPermissionService, $limitUsers);
            $this->syncFighters();
            $this->syncEvents();
            $this->syncRegistrations();

            if (! $this->dryRun) {
                $this->persistStateWatermarks();
                $this->legacy->table('bff_sync_runs')
                    ->where('id', $this->runId)
                    ->update([
                        'finished_at' => now(),
                        'success' => 1,
                        'message' => $this->summaryText(),
                    ]);
            }

            $this->line($this->summaryText());

            return self::SUCCESS;
        } catch (Throwable $exception) {
            if (! $this->dryRun && $this->runId > 0) {
                $this->legacy->table('bff_sync_runs')
                    ->where('id', $this->runId)
                    ->update([
                        'finished_at' => now(),
                        'success' => 0,
                        'message' => $exception->getMessage(),
                    ]);
            }

            $this->error('Legacy sync failed: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }

    private function prepareLegacyLookups(): void
    {
        $userRows = $this->legacy->table('user_daten')
            ->orderByDesc('user_daten_id')
            ->get();

        foreach ($userRows as $row) {
            $userId = (int) $row->user_id;
            if (! isset($this->legacyUserData[$userId])) {
                $this->legacyUserData[$userId] = $row;
            }
        }

        $clubRows = $this->legacy->table('vereine_maps')
            ->orderBy('vereine_maps_id')
            ->get();

        foreach ($clubRows as $row) {
            $key = $this->normalizeClubName((string) $row->vereinsname);
            if ($key !== '' && ! isset($this->legacyClubDirectory[$key])) {
                $this->legacyClubDirectory[$key] = $row;
            }
        }

        $docRows = $this->legacy->table('veranstaltungen_pdf')->get(['veranstaltungen_id', 'bildname']);
        foreach ($docRows as $row) {
            $eventId = (int) $row->veranstaltungen_id;
            $fileName = trim((string) $row->bildname);
            if ($fileName === '') {
                continue;
            }

            $this->legacyEventDocs[$eventId] ??= [];
            $this->legacyEventDocs[$eventId][] = Storage::disk('public')->url('legacy/veranstaltungen_pdf/' . $fileName);
        }
    }

    private function syncUsersAndClubs(ClubPermissionService $clubPermissionService, int $limitUsers): void
    {
        $query = $this->legacy->table('user')->orderByDesc('user_id');

        if ($limitUsers > 0) {
            $query->limit($limitUsers);
        }

        foreach ($query->cursor() as $legacyUser) {
            $legacyUserId = (int) $legacyUser->user_id;
            $userData = $this->legacyUserData[$legacyUserId] ?? null;

            $email = Str::lower(trim((string) $legacyUser->email));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->counts['users_ignored']++;
                $this->markIgnored('user', $legacyUserId, 'invalid_email', 'Email empty or invalid');
                continue;
            }

            if ($this->isObviousTestUser($legacyUser, $userData)) {
                $this->counts['users_ignored']++;
                $this->markIgnored('user', $legacyUserId, 'obvious_test_data', 'Matched test-data filter');
                continue;
            }

            $firstName = $this->cleanText((string) ($userData->vorname ?? ''));
            $lastName = $this->cleanText((string) ($userData->nachname ?? ''));
            $fullName = trim(($firstName !== '' ? $firstName : '') . ' ' . ($lastName !== '' ? $lastName : ''));
            if ($fullName === '') {
                $fullName = $email;
            }

            $legacyPassword = trim((string) $legacyUser->passwort);
            $createdAt = $this->safeDateTime((string) $legacyUser->timestamp) ?? now();
            $phone = $this->cleanText((string) ($userData->telefon ?? ''));

            [$newUserId, $created] = $this->upsertUser(
                $email,
                [
                    'name' => $fullName,
                    'first_name' => $firstName !== '' ? $firstName : null,
                    'last_name' => $lastName !== '' ? $lastName : null,
                    'phone' => $phone !== '' ? mb_substr($phone, 0, 40) : null,
                    'password' => $legacyPassword !== '' ? $legacyPassword : Str::random(40),
                    'email_verified_at' => now(),
                    'email_verification_token' => null,
                    'is_admin_support' => false,
                    'is_super_admin' => false,
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ]
            );

            $created ? $this->counts['users_created']++ : $this->counts['users_updated']++;
            $this->legacyUserToNewUser[$legacyUserId] = $newUserId;

            $this->markMigrated('user', $legacyUserId, $newUserId, (string) $legacyUser->timestamp, [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'club' => (string) ($userData->verein ?? ''),
            ]);

            $legacyClubName = $this->cleanText((string) ($userData->verein ?? ''));
            if ($legacyClubName === '' || $this->isObviousTestClub($legacyClubName)) {
                $this->markIgnored('club', $legacyUserId, 'missing_or_test_club', 'User has no migratable club name');
                continue;
            }

            $club = $this->resolveOrCreateClub($legacyClubName, $legacyUserId, $newUserId, $userData);
            $this->legacyUserToClub[$legacyUserId] = (int) $club->getKey();

            if (! $this->dryRun) {
                $userModel = \App\Models\User::query()->find($newUserId);
                if ($userModel) {
                    $clubPermissionService->addMembership($userModel, (int) $club->getKey(), [
                        ClubMembershipRole::ROLE_CLUB_MANAGER,
                    ]);
                    $this->counts['memberships_created']++;
                }
            }
        }

        if ($this->counts['users_created'] + $this->counts['users_updated'] === 0) {
            $this->warn('No users migrated from legacy source.');
        }
    }

    private function syncFighters(): void
    {
        $rows = $this->legacy->table('kaempfer')
            ->where('status', 1)
            ->orderBy('kaempfer_id')
            ->cursor();

        foreach ($rows as $legacyFighter) {
            $legacyFighterId = (int) $legacyFighter->kaempfer_id;
            $legacyOwnerId = (int) $legacyFighter->user_id;

            $clubId = $this->legacyUserToClub[$legacyOwnerId] ?? null;
            $createdBy = $this->legacyUserToNewUser[$legacyOwnerId] ?? null;

            if (! $clubId) {
                $this->counts['fighters_ignored']++;
                $this->markIgnored('fighter', $legacyFighterId, 'missing_club', 'No mapped club found for owner user');
                continue;
            }

            $payload = [
                'club_id' => $clubId,
                'created_by_user_id' => $createdBy,
                'first_name' => $this->fallbackName((string) $legacyFighter->vorname, 'Unknown'),
                'last_name' => $this->fallbackName((string) $legacyFighter->nachname, 'Legacy'),
                'birth_date' => $this->safeDate((string) $legacyFighter->geburtsdatum),
                'sex' => $this->mapSex((string) $legacyFighter->geschlecht),
                'weight_class' => (string) $legacyFighter->gewichtsklasse,
                'sport_modules' => json_encode(['boxing'], JSON_THROW_ON_ERROR),
                'boxing_weight_entries' => json_encode([
                    ['weight' => (float) ($legacyFighter->gewichtsklasse ?? 0), 'source' => 'legacy'],
                ], JSON_THROW_ON_ERROR),
                'boxing_bout_count_entries' => json_encode([
                    [
                        'wins' => (int) $legacyFighter->siege,
                        'losses' => (int) $legacyFighter->niederlagen,
                        'draws' => (int) $legacyFighter->unentschieden,
                        'source' => 'legacy',
                    ],
                ], JSON_THROW_ON_ERROR),
                'status' => 'active',
                'updated_at' => now(),
            ];

            $legacyTs = $this->safeDateTime((string) $legacyFighter->timestamp) ?? now();
            $payload['created_at'] = $legacyTs;

            [$newId, $created] = $this->upsertMappedRecord('fighter', $legacyFighterId, 'fighters', $payload, 'kaempfer_id', (string) $legacyFighter->timestamp);

            $created ? $this->counts['fighters_created']++ : $this->counts['fighters_updated']++;
            $this->legacyFighterToNew[$legacyFighterId] = $newId;
        }
    }

    private function syncEvents(): void
    {
        $rows = $this->legacy->table('veranstaltungen')->orderBy('veranstaltungen_id')->cursor();

        foreach ($rows as $legacyEvent) {
            $legacyEventId = (int) $legacyEvent->veranstaltungen_id;
            $title = $this->cleanText((string) $legacyEvent->name);

            if ($title === '' || $this->isObviousTestEvent($title)) {
                $this->counts['events_ignored']++;
                $this->markIgnored('event', $legacyEventId, 'test_or_empty_title', 'Event title filtered');
                continue;
            }

            $legacyOrganizerId = (int) $legacyEvent->veranstalter_id;
            $organizerClubId = $this->legacyUserToClub[$legacyOrganizerId] ?? null;
            $createdBy = $this->legacyUserToNewUser[$legacyOrganizerId] ?? null;

            $startDate = $this->safeDate((string) $legacyEvent->datum);
            if (! $startDate) {
                $this->counts['events_ignored']++;
                $this->markIgnored('event', $legacyEventId, 'invalid_date', 'Invalid event date');
                continue;
            }

            $deadline = $this->safeDate((string) $legacyEvent->anmeldeschluss);
            $entryFee = (int) round(((float) ($legacyEvent->startgebuehr ?? 0)) * 100);

            $payload = [
                'title' => $title,
                'description' => null,
                'starts_at' => Carbon::parse($startDate)->setTime(9, 0, 0),
                'ends_at' => null,
                'registration_deadline' => $deadline ? Carbon::parse($deadline)->endOfDay() : null,
                'registration_approval_mode' => 'auto',
                'max_registrations' => ((int) $legacyEvent->teilnehmermax) > 0 ? (int) $legacyEvent->teilnehmermax : null,
                'allow_waitlist' => false,
                'billing_locked_at' => null,
                'entry_fee_cents' => $entryFee >= 0 ? $entryFee : 0,
                'currency' => 'EUR',
                'info_documents' => json_encode($this->legacyEventDocs[$legacyEventId] ?? [], JSON_THROW_ON_ERROR),
                'location' => $this->cleanText((string) $legacyEvent->ort) ?: null,
                'sport_module' => 'boxing',
                'venue_name' => null,
                'address_line1' => null,
                'address_line2' => null,
                'postal_code' => null,
                'city' => null,
                'country' => 'DE',
                'boxing_package_key' => null,
                'boxing_age_classes' => null,
                'boxing_sexes' => null,
                'boxing_performance_classes' => null,
                'status' => 'published',
                'published_at' => now(),
                'organizer_club_id' => $organizerClubId,
                'created_by_user_id' => $createdBy,
                'cancelled_at' => null,
                'cancel_reason' => null,
                'updated_at' => now(),
            ];

            $legacyTs = $this->safeDateTime((string) $legacyEvent->timestamp) ?? now();
            $payload['created_at'] = $legacyTs;

            [$newId, $created] = $this->upsertMappedRecord('event', $legacyEventId, 'events', $payload, 'veranstaltungen_id', (string) $legacyEvent->timestamp);

            $created ? $this->counts['events_created']++ : $this->counts['events_updated']++;
            $this->legacyEventToNew[$legacyEventId] = $newId;
        }
    }

    private function syncRegistrations(): void
    {
        $rows = $this->legacy->table('einschreibungen')->orderBy('einschreibungen_id')->cursor();

        foreach ($rows as $legacyRegistration) {
            $legacyRegistrationId = (int) $legacyRegistration->einschreibungen_id;
            $legacyEventId = (int) $legacyRegistration->veranstaltung_id;
            $legacyFighterId = (int) $legacyRegistration->kaempfer_id;

            $eventId = $this->legacyEventToNew[$legacyEventId] ?? null;
            $fighterId = $this->legacyFighterToNew[$legacyFighterId] ?? null;
            $registeredBy = $this->legacyUserToNewUser[(int) $legacyRegistration->user_id] ?? null;

            if (! $eventId || ! $fighterId) {
                $this->counts['registrations_ignored']++;
                $this->markIgnored('registration', $legacyRegistrationId, 'missing_event_or_fighter', 'Referenced event or fighter not migrated');
                continue;
            }

            $status = ((int) $legacyRegistration->status) === 1
                ? Registration::STATUS_ACTIVE
                : Registration::STATUS_WITHDRAWN;

            $legacyTs = $this->safeDateTime((string) $legacyRegistration->timestamp) ?? now();

            $snapshot = [
                'legacy_registration_id' => $legacyRegistrationId,
                'legacy_event_id' => $legacyEventId,
                'legacy_fighter_id' => $legacyFighterId,
                'legacy_weight' => $legacyRegistration->gewicht,
                'legacy_weight_class' => $legacyRegistration->gewichtsklasse,
                'legacy_performance_class' => $legacyRegistration->leistungsklasse,
                'legacy_wins' => (int) $legacyRegistration->siege,
                'legacy_losses' => (int) $legacyRegistration->niederlagen,
                'legacy_draws' => (int) $legacyRegistration->unentschieden,
            ];

            $payload = [
                'fighter_id' => $fighterId,
                'event_id' => $eventId,
                'status' => $status,
                'registered_by_user_id' => $registeredBy,
                'notes' => 'Legacy import #' . $legacyRegistrationId,
                'fighter_snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR),
                'billable_at' => null,
                'billable_reason' => null,
                'withdrawn_at' => $status === Registration::STATUS_WITHDRAWN ? $legacyTs : null,
                'status_changed_at' => $legacyTs,
                'created_at' => $legacyTs,
                'updated_at' => now(),
            ];

            [$newId, $created] = $this->upsertRegistration($legacyRegistrationId, $payload, (string) $legacyRegistration->timestamp);
            $created ? $this->counts['registrations_created']++ : $this->counts['registrations_updated']++;

            if (! $this->dryRun) {
                $this->target->table('registration_status_histories')->insert([
                    'registration_id' => $newId,
                    'from_status' => null,
                    'to_status' => $status,
                    'changed_by_user_id' => $registeredBy,
                    'reason' => 'legacy_import',
                    'meta' => json_encode(['legacy_registration_id' => $legacyRegistrationId], JSON_THROW_ON_ERROR),
                    'created_at' => $legacyTs,
                    'updated_at' => $legacyTs,
                ]);
            }
        }
    }

    /**
     * @return array{0:int,1:bool}
     */
    private function upsertUser(string $email, array $payload): array
    {
        $existing = $this->target->table('users')->where('email', $email)->first(['id']);

        if ($existing) {
            if (! $this->dryRun) {
                $this->target->table('users')->where('id', $existing->id)->update($payload);
            }

            return [(int) $existing->id, false];
        }

        if (! $this->dryRun) {
            $id = (int) $this->target->table('users')->insertGetId($payload);
            return [$id, true];
        }

        return [0, true];
    }

    private function resolveOrCreateClub(string $legacyClubName, int $legacyUserId, int $newUserId, ?object $userData): Club
    {
        $exact = trim($legacyClubName);
        if (isset($this->clubByExactName[$exact])) {
            $clubId = $this->clubByExactName[$exact];
            $this->counts['clubs_reused']++;

            if ($this->dryRun || $clubId <= 0) {
                $club = new Club();
                $club->id = 0;
                $club->name = $exact;
                $club->slug = Str::slug($exact) ?: 'club-' . $legacyUserId;
            } else {
                /** @var Club $club */
                $club = Club::query()->findOrFail($clubId);
            }

            $this->markMigrated('club', $legacyUserId, $clubId, (string) ($userData->timestamp ?? ''), [
                'club_name' => $exact,
                'reuse' => true,
            ]);

            return $club;
        }

        $normalized = $this->normalizeClubName($exact);
        $directory = $normalized !== '' ? ($this->legacyClubDirectory[$normalized] ?? null) : null;

        $baseSlug = Str::slug($exact);
        if ($baseSlug === '') {
            $baseSlug = 'club-' . $legacyUserId;
        }

        $slug = $this->buildUniqueClubSlug($baseSlug);

        $payload = [
            'name' => $exact,
            'slug' => $slug,
            'description' => null,
            'billing_company_name' => $exact,
            'billing_contact_name' => trim($this->cleanText((string) ($userData->vorname ?? '')) . ' ' . $this->cleanText((string) ($userData->nachname ?? ''))),
            'billing_email' => null,
            'billing_address_line1' => $this->cleanText((string) ($directory->strasse ?? '')),
            'billing_address_line2' => isset($directory->hausnummer) ? (string) $directory->hausnummer : null,
            'billing_zip' => $this->cleanText((string) ($directory->plz ?? '')),
            'billing_city' => $this->cleanText((string) ($directory->ort ?? '')),
            'billing_country' => 'DE',
            'created_by_user_id' => $newUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (! $this->dryRun) {
            $clubId = (int) $this->target->table('clubs')->insertGetId($payload);
            $club = Club::query()->findOrFail($clubId);
        } else {
            $club = new Club();
            $club->id = 0;
        }

        $this->clubByExactName[$exact] = (int) $club->getKey();
        $this->counts['clubs_created']++;
        $this->markMigrated('club', $legacyUserId, (int) $club->getKey(), (string) ($userData->timestamp ?? ''), [
            'club_name' => $exact,
            'slug' => $slug,
            'reuse' => false,
        ]);

        return $club;
    }

    /**
     * @return array{0:int,1:bool}
     */
    private function upsertMappedRecord(string $entity, int $legacyId, string $table, array $payload, string $legacyIdField, string $legacyTimestamp): array
    {
        $mapped = $this->legacy->table('bff_sync_map')
            ->where('entity', $entity)
            ->where('legacy_id', $legacyId)
            ->first();

        if ($mapped && (int) $mapped->new_id > 0) {
            $newId = (int) $mapped->new_id;
            if (! $this->dryRun) {
                $this->target->table($table)->where('id', $newId)->update($payload);
            }

            $this->markMigrated($entity, $legacyId, $newId, $legacyTimestamp, $payload);
            return [$newId, false];
        }

        if (! $this->dryRun) {
            $newId = (int) $this->target->table($table)->insertGetId($payload);
            $this->markMigrated($entity, $legacyId, $newId, $legacyTimestamp, $payload);
            return [$newId, true];
        }

        return [0, true];
    }

    /**
     * @return array{0:int,1:bool}
     */
    private function upsertRegistration(int $legacyRegistrationId, array $payload, string $legacyTimestamp): array
    {
        $mapped = $this->legacy->table('bff_sync_map')
            ->where('entity', 'registration')
            ->where('legacy_id', $legacyRegistrationId)
            ->first();

        if ($mapped && (int) $mapped->new_id > 0) {
            $newId = (int) $mapped->new_id;
            if (! $this->dryRun) {
                $this->target->table('registrations')->where('id', $newId)->update($payload);
            }

            $this->markMigrated('registration', $legacyRegistrationId, $newId, $legacyTimestamp, $payload);
            return [$newId, false];
        }

        $existing = $this->target->table('registrations')
            ->where('fighter_id', $payload['fighter_id'])
            ->where('event_id', $payload['event_id'])
            ->first(['id']);

        if ($existing) {
            $newId = (int) $existing->id;
            if (! $this->dryRun) {
                $this->target->table('registrations')->where('id', $newId)->update($payload);
            }

            $this->markMigrated('registration', $legacyRegistrationId, $newId, $legacyTimestamp, $payload);
            return [$newId, false];
        }

        if (! $this->dryRun) {
            $newId = (int) $this->target->table('registrations')->insertGetId($payload);
            $this->markMigrated('registration', $legacyRegistrationId, $newId, $legacyTimestamp, $payload);
            return [$newId, true];
        }

        return [0, true];
    }

    private function markMigrated(string $entity, int $legacyId, int $newId, string $legacyTimestamp, array $payload): void
    {
        if ($this->dryRun) {
            return;
        }

        $checksum = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));

        $this->legacy->table('bff_sync_map')->updateOrInsert(
            ['entity' => $entity, 'legacy_id' => $legacyId],
            [
                'new_id' => $newId,
                'source_updated_at' => $this->safeDateTime($legacyTimestamp),
                'source_checksum' => $checksum,
                'sync_status' => 'migrated',
                'note' => null,
                'migrated_at' => now(),
                'last_synced_at' => now(),
            ]
        );
    }

    private function markIgnored(string $entity, int $legacyId, string $reason, string $details): void
    {
        if ($this->dryRun) {
            return;
        }

        $this->legacy->table('bff_sync_map')->updateOrInsert(
            ['entity' => $entity, 'legacy_id' => $legacyId],
            [
                'new_id' => null,
                'source_updated_at' => null,
                'source_checksum' => null,
                'sync_status' => 'ignored',
                'note' => $reason,
                'last_synced_at' => now(),
            ]
        );

        $this->legacy->table('bff_sync_ignored')->updateOrInsert(
            ['entity' => $entity, 'legacy_id' => $legacyId],
            [
                'reason' => $reason,
                'details' => $details,
                'decided_at' => now(),
            ]
        );
    }

    private function persistStateWatermarks(): void
    {
        $sources = [
            ['table' => 'user', 'id' => 'user_id', 'ts' => 'timestamp'],
            ['table' => 'user_daten', 'id' => 'user_daten_id', 'ts' => 'timestamp'],
            ['table' => 'kaempfer', 'id' => 'kaempfer_id', 'ts' => 'timestamp'],
            ['table' => 'veranstaltungen', 'id' => 'veranstaltungen_id', 'ts' => 'timestamp'],
            ['table' => 'einschreibungen', 'id' => 'einschreibungen_id', 'ts' => 'timestamp'],
        ];

        foreach ($sources as $source) {
            $row = $this->legacy->table($source['table'])
                ->selectRaw('MAX(' . $source['id'] . ') AS max_id, MAX(' . $source['ts'] . ') AS max_ts')
                ->first();

            $this->legacy->table('bff_sync_state')->updateOrInsert(
                ['source_table' => $source['table']],
                [
                    'last_source_timestamp' => $this->safeDateTime((string) ($row->max_ts ?? '')),
                    'last_source_id' => (int) ($row->max_id ?? 0),
                    'last_run_id' => $this->runId,
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function summaryText(): string
    {
        return implode(' | ', [
            'users c/u/i: ' . $this->counts['users_created'] . '/' . $this->counts['users_updated'] . '/' . $this->counts['users_ignored'],
            'clubs c/r: ' . $this->counts['clubs_created'] . '/' . $this->counts['clubs_reused'],
            'memberships: ' . $this->counts['memberships_created'],
            'fighters c/u/i: ' . $this->counts['fighters_created'] . '/' . $this->counts['fighters_updated'] . '/' . $this->counts['fighters_ignored'],
            'events c/u/i: ' . $this->counts['events_created'] . '/' . $this->counts['events_updated'] . '/' . $this->counts['events_ignored'],
            'regs c/u/i: ' . $this->counts['registrations_created'] . '/' . $this->counts['registrations_updated'] . '/' . $this->counts['registrations_ignored'],
        ]);
    }

    private function buildUniqueClubSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $suffix = 1;

        while ($this->target->table('clubs')->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $baseSlug . '-' . $suffix;
        }

        return $slug;
    }

    private function normalizeClubName(string $value): string
    {
        return trim(Str::of($value)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->toString());
    }

    private function cleanText(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    private function fallbackName(string $value, string $fallback): string
    {
        $clean = $this->cleanText($value);
        return $clean !== '' ? mb_substr($clean, 0, 120) : $fallback;
    }

    private function mapSex(string $legacySex): ?string
    {
        $value = Str::lower($this->cleanText($legacySex));
        if (str_starts_with($value, 'm')) {
            return 'm';
        }
        if (str_starts_with($value, 'w') || str_starts_with($value, 'f')) {
            return 'f';
        }

        return null;
    }

    private function safeDateTime(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function safeDate(string $value): ?Carbon
    {
        $dt = $this->safeDateTime($value);
        return $dt?->startOfDay();
    }

    private function isObviousTestUser(object $legacyUser, ?object $userData): bool
    {
        $haystack = Str::lower(implode(' ', [
            (string) $legacyUser->email,
            (string) ($userData->vorname ?? ''),
            (string) ($userData->nachname ?? ''),
            (string) ($userData->verein ?? ''),
        ]));

        foreach (['test', 'dummy', 'nicht anmelden', 'beispiel', 'example', 'foo@', 'bar@'] as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isObviousTestClub(string $clubName): bool
    {
        $value = Str::lower($clubName);
        foreach (['test', 'nicht anmelden', 'dummy', 'beispiel'] as $needle) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function isObviousTestEvent(string $title): bool
    {
        $value = Str::lower($title);
        foreach (['test', 'nicht anmelden', 'probe', 'wartungsarbeiten'] as $needle) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }
}
