<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\ClubJoinRequest;
use App\Models\ClubMembership;
use App\Models\ClubMembershipRole;
use App\Models\Event;
use App\Models\Fighter;
use App\Models\User;
use App\Services\ClubPermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = app(ClubPermissionService::class);
        $demoBatch = 'seed-default';

        // â”€â”€ Platform accounts â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@baseforfight.test'],
            [
                'name'              => 'BaseForFight Admin',
                'first_name'        => 'BaseForFight',
                'last_name'         => 'Admin',
                'password'          => Hash::make('admin1234'),
                'email_verified_at' => now(),
                'is_admin_support'  => true,
                'is_super_admin'    => false,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'superadmin@baseforfight.test'],
            [
                'name'              => 'BaseForFight SuperAdmin',
                'first_name'        => 'BaseForFight',
                'last_name'         => 'SuperAdmin',
                'password'          => Hash::make('superadmin1234'),
                'email_verified_at' => now(),
                'is_admin_support'  => true,
                'is_super_admin'    => true,
            ]
        );

        // â”€â”€ HQ Seed-Club (admin is club_manager) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $hqClub = Club::query()->updateOrCreate(
            ['slug' => 'baseforfight-hq'],
            [
                'name'                  => 'BaseForFight HQ',
                'description'           => 'Interner Seed-Verein.',
                'billing_company_name'  => 'BaseForFight HQ e.V.',
                'billing_contact_name'  => 'BaseForFight Admin',
                'billing_email'         => 'billing@baseforfight.test',
                'billing_address_line1' => 'Ringstrasse 1',
                'billing_zip'           => '10115',
                'billing_city'          => 'Berlin',
                'billing_country'       => 'DE',
                'created_by_user_id'    => $admin->getKey(),
            ]
        );

        $this->ensureMembership($permissions, $admin, $hqClub, [ClubMembershipRole::ROLE_CLUB_MANAGER]);

        // â”€â”€ Club blueprints â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $blueprints = [
            [
                'club' => [
                    'slug'                  => 'blue-corner-gym',
                    'name'                  => 'Blue Corner Gym',
                    'description'           => 'Boxclub mit gemischten Rollen-Demo.',
                    'billing_company_name'  => 'Blue Corner Gym e.V.',
                    'billing_contact_name'  => 'Blue Corner Manager',
                    'billing_email'         => 'billing@bluecorner.test',
                    'billing_address_line1' => 'Jabweg 12',
                    'billing_zip'           => '20095',
                    'billing_city'          => 'Hamburg',
                    'billing_country'       => 'DE',
                ],
                'users' => [
                    [
                        'name'       => 'Blue Corner Manager',
                        'first_name' => 'Blue Corner',
                        'last_name'  => 'Manager',
                        'email'      => 'manager@bluecorner.test',
                        'password'   => 'manager1234',
                        'roles'      => [ClubMembershipRole::ROLE_CLUB_MANAGER],
                    ],
                    [
                        'name'       => 'Blue Corner Eventmanager',
                        'first_name' => 'Blue Corner',
                        'last_name'  => 'Eventmanager',
                        'email'      => 'events@bluecorner.test',
                        'password'   => 'events1234',
                        'roles'      => [ClubMembershipRole::ROLE_EVENT_MANAGER],
                    ],
                    [
                        'name'       => 'Blue Corner Trainer 1',
                        'first_name' => 'Trainer',
                        'last_name'  => 'Eins',
                        'email'      => 'trainer1@bluecorner.test',
                        'password'   => 'trainer1234',
                        'roles'      => [ClubMembershipRole::ROLE_TRAINER],
                    ],
                    [
                        'name'       => 'Blue Corner Trainer 2',
                        'first_name' => 'Trainer',
                        'last_name'  => 'Zwei',
                        'email'      => 'trainer2@bluecorner.test',
                        'password'   => 'trainer1234',
                        // Multi-role: auch Eventmanager
                        'roles'      => [ClubMembershipRole::ROLE_TRAINER, ClubMembershipRole::ROLE_EVENT_MANAGER],
                    ],
                ],
                'fighters' => [
                    ['first_name' => 'Max', 'last_name' => 'Mittelgewicht', 'birth_date' => '1998-04-12', 'weight' => 69.5],
                    ['first_name' => 'Lea', 'last_name' => 'Links', 'birth_date' => '2001-09-03', 'weight' => 57.2],
                    ['first_name' => 'Aylin', 'last_name' => 'Kaya', 'birth_date' => '2005-08-18', 'weight' => 50.8],
                    ['first_name' => 'Ruben', 'last_name' => 'Kraft', 'birth_date' => '2001-03-04', 'weight' => 63.7],
                ],
            ],
            [
                'club' => [
                    'slug'                  => 'iron-house-club',
                    'name'                  => 'Iron House Club',
                    'description'           => 'Democlub fuer Manager- und Trainer-Ansicht.',
                    'billing_company_name'  => 'Iron House Club',
                    'billing_contact_name'  => 'Iron House Manager',
                    'billing_email'         => 'billing@ironhouse.test',
                    'billing_address_line1' => 'Kampfring 7',
                    'billing_address_line2' => '2. Etage',
                    'billing_zip'           => '50667',
                    'billing_city'          => 'Koeln',
                    'billing_country'       => 'DE',
                ],
                'users' => [
                    [
                        'name'       => 'Iron House Manager',
                        'first_name' => 'Iron',
                        'last_name'  => 'Manager',
                        'email'      => 'manager@ironhouse.test',
                        'password'   => 'manager1234',
                        'roles'      => [ClubMembershipRole::ROLE_CLUB_MANAGER, ClubMembershipRole::ROLE_EVENT_MANAGER],
                    ],
                    [
                        'name'       => 'Iron House Trainer',
                        'first_name' => 'Iron',
                        'last_name'  => 'Trainer',
                        'email'      => 'trainer@ironhouse.test',
                        'password'   => 'trainer1234',
                        'roles'      => [ClubMembershipRole::ROLE_TRAINER],
                    ],
                ],
                'fighters' => [
                    ['first_name' => 'Tom', 'last_name' => 'Schwer', 'birth_date' => '1995-01-20', 'weight' => 79.8],
                    ['first_name' => 'Sara', 'last_name' => 'Stark', 'birth_date' => '2002-07-11', 'weight' => 60.1],
                ],
            ],
            [
                'club' => [
                    'slug'                  => 'spree-warriors-berlin',
                    'name'                  => 'Spree Warriors Berlin',
                    'description'           => 'Democlub fuer Event-Meldungen und Teamverwaltung.',
                    'billing_company_name'  => 'Spree Warriors Berlin e.V.',
                    'billing_contact_name'  => 'Marlon Becker',
                    'billing_email'         => 'billing@spree-warriors.test',
                    'billing_address_line1' => 'Spreering 8',
                    'billing_zip'           => '10243',
                    'billing_city'          => 'Berlin',
                    'billing_country'       => 'DE',
                ],
                'users' => [
                    [
                        'name'       => 'Marlon Becker',
                        'first_name' => 'Marlon',
                        'last_name'  => 'Becker',
                        'email'      => 'manager@spree-warriors.test',
                        'password'   => 'manager1234',
                        'roles'      => [ClubMembershipRole::ROLE_CLUB_MANAGER],
                    ],
                    [
                        'name'       => 'Kira Feld',
                        'first_name' => 'Kira',
                        'last_name'  => 'Feld',
                        'email'      => 'trainer1@spree-warriors.test',
                        'password'   => 'trainer1234',
                        'roles'      => [ClubMembershipRole::ROLE_TRAINER],
                    ],
                    [
                        'name'       => 'Nico Sturm',
                        'first_name' => 'Nico',
                        'last_name'  => 'Sturm',
                        'email'      => 'trainer2@spree-warriors.test',
                        'password'   => 'trainer1234',
                        'roles'      => [ClubMembershipRole::ROLE_TRAINER, ClubMembershipRole::ROLE_EVENT_MANAGER],
                    ],
                ],
                'fighters' => [
                    ['first_name' => 'Mina', 'last_name' => 'Tas', 'birth_date' => '1999-11-29', 'weight' => 57.9],
                    ['first_name' => 'Jaro', 'last_name' => 'Ilic', 'birth_date' => '1994-06-12', 'weight' => 74.3],
                ],
            ],
        ];

        foreach ($blueprints as $blueprint) {
            $managerUserData = collect($blueprint['users'])->first(
                fn ($u) => in_array(ClubMembershipRole::ROLE_CLUB_MANAGER, $u['roles'], true)
            );

            $managerUser = User::query()->updateOrCreate(
                ['email' => $managerUserData['email']],
                [
                    'name'              => $managerUserData['name'],
                    'first_name'        => $managerUserData['first_name'],
                    'last_name'         => $managerUserData['last_name'],
                    'password'          => Hash::make($managerUserData['password']),
                    'email_verified_at' => now(),
                    'is_admin_support'  => false,
                    'is_super_admin'    => false,
                    'is_demo'           => true,
                    'demo_batch'        => $demoBatch,
                ]
            );

            $club = Club::query()->updateOrCreate(
                ['slug' => $blueprint['club']['slug']],
                array_merge($blueprint['club'], [
                    'created_by_user_id' => $managerUser->getKey(),
                    'is_demo' => true,
                    'demo_batch' => $demoBatch,
                ])
            );

            foreach ($blueprint['users'] as $userData) {
                $user = User::query()->updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name'              => $userData['name'],
                        'first_name'        => $userData['first_name'],
                        'last_name'         => $userData['last_name'],
                        'password'          => Hash::make($userData['password']),
                        'email_verified_at' => now(),
                        'is_admin_support'  => false,
                        'is_super_admin'    => false,
                        'is_demo'           => true,
                        'demo_batch'        => $demoBatch,
                    ]
                );

                $this->ensureMembership($permissions, $user, $club, $userData['roles']);
            }

            $trainerIds = ClubMembership::query()
                ->where('club_id', $club->getKey())
                ->whereHas('roles', fn ($q) => $q->where('role', ClubMembershipRole::ROLE_TRAINER))
                ->pluck('user_id')
                ->toArray();

            foreach ($blueprint['fighters'] as $index => $fighterData) {
                $creatorId = $trainerIds[$index % max(count($trainerIds), 1)] ?? $managerUser->getKey();

                Fighter::query()->updateOrCreate(
                    [
                        'club_id'    => $club->getKey(),
                        'first_name' => $fighterData['first_name'],
                        'last_name'  => $fighterData['last_name'],
                    ],
                    [
                        'created_by_user_id'       => $creatorId,
                        'birth_date'               => $fighterData['birth_date'],
                        'weight_class'             => null,
                        'sport_modules'            => ['boxing'],
                        'boxing_weight_entries'    => [[
                            'date'      => now()->toDateString(),
                            'weight_kg' => $fighterData['weight'],
                        ]],
                        'boxing_bout_count_entries' => [[
                            'date'   => now()->toDateString(),
                            'wins'   => 0,
                            'losses' => 0,
                            'draws'  => 0,
                        ]],
                        'boxing_pass_entries' => [[
                            'keyword' => 'Arzt gueltig bis',
                            'date'    => now()->addMonths(6)->toDateString(),
                        ]],
                        'status' => 'active',
                        'is_demo' => true,
                        'demo_batch' => $demoBatch,
                    ]
                );
            }

            $this->seedDemoEvents($club, $managerUser, $demoBatch);
        }

        // â”€â”€ Demo Join-Requests â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $demoApplicant = User::query()->updateOrCreate(
            ['email' => 'applicant@demo.test'],
            [
                'name'              => 'Demo Bewerber',
                'first_name'        => 'Demo',
                'last_name'         => 'Bewerber',
                'password'          => Hash::make('demo1234'),
                'email_verified_at' => now(),
                'is_admin_support'  => false,
                'is_super_admin'    => false,
                'is_demo'           => true,
                'demo_batch'        => $demoBatch,
            ]
        );

        $blueCorner = Club::query()->where('slug', 'blue-corner-gym')->first();
        $ironHouse  = Club::query()->where('slug', 'iron-house-club')->first();

        if ($blueCorner) {
            ClubJoinRequest::query()->updateOrCreate([
                'club_id'             => $blueCorner->getKey(),
                'user_id'             => $demoApplicant->getKey(),
            ], [
                'requested_club_name' => $blueCorner->name,
                'requested_club_slug' => $blueCorner->slug,
                'status'              => 'pending',
                'is_demo'             => true,
                'demo_batch'          => $demoBatch,
            ]);
        }

        $demoApplicant2 = User::query()->updateOrCreate(
            ['email' => 'applicant2@demo.test'],
            [
                'name'              => 'Zweiter Bewerber',
                'first_name'        => 'Zweiter',
                'last_name'         => 'Bewerber',
                'password'          => Hash::make('demo1234'),
                'email_verified_at' => now(),
                'is_admin_support'  => false,
                'is_super_admin'    => false,
                'is_demo'           => true,
                'demo_batch'        => $demoBatch,
            ]
        );

        if ($ironHouse) {
            ClubJoinRequest::query()->updateOrCreate([
                'club_id'             => $ironHouse->getKey(),
                'user_id'             => $demoApplicant2->getKey(),
            ], [
                'requested_club_name' => $ironHouse->name,
                'requested_club_slug' => $ironHouse->slug,
                'status'              => 'pending',
                'is_demo'             => true,
                'demo_batch'          => $demoBatch,
            ]);
        }
    }

    private function seedDemoEvents(Club $club, User $managerUser, string $demoBatch): void
    {
        $startsAt = now()->addDays(14)->setTime(10, 0);
        $followUpStartsAt = now()->addDays(35)->setTime(11, 0);

        $events = [
            [
                'title' => 'Demo Sparring ' . $club->name,
                'starts_at' => $startsAt,
                'ends_at' => (clone $startsAt)->addHours(6),
                'registration_deadline' => (clone $startsAt)->subDays(4),
                'status' => 'published',
                'entry_fee_cents' => 1500,
                'venue_name' => $club->name,
                'address_line1' => $club->billing_address_line1,
                'address_line2' => $club->billing_address_line2,
                'postal_code' => $club->billing_zip,
                'city' => $club->billing_city,
                'country' => $club->billing_country,
                'allow_waitlist' => true,
                'max_registrations' => 24,
            ],
            [
                'title' => 'Demo Turniertag ' . $club->name,
                'starts_at' => $followUpStartsAt,
                'ends_at' => (clone $followUpStartsAt)->addHours(8),
                'registration_deadline' => (clone $followUpStartsAt)->subDays(7),
                'status' => 'draft',
                'entry_fee_cents' => 2500,
                'venue_name' => $club->name,
                'address_line1' => $club->billing_address_line1,
                'address_line2' => $club->billing_address_line2,
                'postal_code' => $club->billing_zip,
                'city' => $club->billing_city,
                'country' => $club->billing_country,
                'allow_waitlist' => false,
                'max_registrations' => 16,
            ],
        ];

        foreach ($events as $eventData) {
            Event::query()->updateOrCreate(
                [
                    'organizer_club_id' => $club->getKey(),
                    'title' => $eventData['title'],
                ],
                [
                    'description' => 'Demoveranstaltung fuer Tests, UI-Abnahmen und Rollenpruefungen.',
                    'starts_at' => $eventData['starts_at'],
                    'ends_at' => $eventData['ends_at'],
                    'registration_deadline' => $eventData['registration_deadline'],
                    'max_registrations' => $eventData['max_registrations'],
                    'allow_waitlist' => $eventData['allow_waitlist'],
                    'entry_fee_cents' => $eventData['entry_fee_cents'],
                    'currency' => 'EUR',
                    'sport_module' => 'boxing',
                    'venue_name' => $eventData['venue_name'],
                    'address_line1' => $eventData['address_line1'],
                    'address_line2' => $eventData['address_line2'],
                    'postal_code' => $eventData['postal_code'],
                    'city' => $eventData['city'],
                    'country' => $eventData['country'],
                    'boxing_package_key' => 'boxing',
                    'boxing_age_classes' => ['u17', 'adults'],
                    'boxing_sexes' => ['m', 'w'],
                    'boxing_performance_classes' => ['amateur'],
                    'status' => $eventData['status'],
                    'published_at' => $eventData['status'] === 'published' ? now() : null,
                    'organizer_club_id' => $club->getKey(),
                    'created_by_user_id' => $managerUser->getKey(),
                    'is_demo' => true,
                    'demo_batch' => $demoBatch,
                ]
            );
        }
    }

    private function ensureMembership(ClubPermissionService $permissions, User $user, Club $club, array $roles): void
    {
        $membership = ClubMembership::query()->firstOrCreate(
            ['club_id' => $club->getKey(), 'user_id' => $user->getKey()],
            ['joined_at' => now()]
        );

        foreach ($roles as $role) {
            ClubMembershipRole::query()->firstOrCreate([
                'club_membership_id' => $membership->getKey(),
                'role'               => $role,
            ]);
        }
    }
}

