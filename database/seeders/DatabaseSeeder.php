<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Fighter;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@baseforfight.test'],
            [
                'name' => 'BaseForFight Admin',
                'password' => Hash::make('admin1234'),
                'email_verified_at' => now(),
                'is_admin_support' => true,
                'is_super_admin' => false,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'superadmin@baseforfight.test'],
            [
                'name' => 'BaseForFight SuperAdmin',
                'password' => Hash::make('superadmin1234'),
                'email_verified_at' => now(),
                'is_admin_support' => true,
                'is_super_admin' => true,
            ]
        );

        $club = Club::query()->updateOrCreate(
            ['slug' => 'baseforfight-hq'],
            [
                'name' => 'BaseForFight HQ',
                'description' => 'Seed club for local admin access.',
                'billing_company_name' => 'BaseForFight HQ e.V.',
                'billing_contact_name' => 'BaseForFight Admin',
                'billing_email' => 'billing@baseforfight.test',
                'billing_address_line1' => 'Ringstrasse 1',
                'billing_address_line2' => null,
                'billing_zip' => '10115',
                'billing_city' => 'Berlin',
                'billing_country' => 'DE',
                'created_by_user_id' => $admin->getKey(),
            ]
        );

        $blueCornerManager = User::query()->updateOrCreate(
            ['email' => 'manager@bluecorner.test'],
            [
                'name' => 'Blue Corner Manager',
                'password' => Hash::make('manager1234'),
                'email_verified_at' => now(),
                'is_admin_support' => false,
                'is_super_admin' => false,
            ]
        );

        $blueCornerTrainer1 = User::query()->updateOrCreate(
            ['email' => 'trainer1@bluecorner.test'],
            [
                'name' => 'Blue Corner Trainer 1',
                'password' => Hash::make('trainer1234'),
                'email_verified_at' => now(),
                'is_admin_support' => false,
                'is_super_admin' => false,
            ]
        );

        $blueCornerTrainer2 = User::query()->updateOrCreate(
            ['email' => 'trainer2@bluecorner.test'],
            [
                'name' => 'Blue Corner Trainer 2',
                'password' => Hash::make('trainer1234'),
                'email_verified_at' => now(),
                'is_admin_support' => false,
                'is_super_admin' => false,
            ]
        );

        $blueCorner = Club::query()->updateOrCreate(
            ['slug' => 'blue-corner-gym'],
            [
                'name' => 'Blue Corner Gym',
                'description' => 'Boxclub mit mehreren Trainern und klaren Rollen.',
                'billing_company_name' => 'Blue Corner Gym e.V.',
                'billing_contact_name' => 'Blue Corner Manager',
                'billing_email' => 'billing@bluecorner.test',
                'billing_address_line1' => 'Jabweg 12',
                'billing_address_line2' => null,
                'billing_zip' => '20095',
                'billing_city' => 'Hamburg',
                'billing_country' => 'DE',
                'created_by_user_id' => $blueCornerManager->getKey(),
            ]
        );

        $ironHouseManager = User::query()->updateOrCreate(
            ['email' => 'manager@ironhouse.test'],
            [
                'name' => 'Iron House Manager',
                'password' => Hash::make('manager1234'),
                'email_verified_at' => now(),
                'is_admin_support' => false,
                'is_super_admin' => false,
            ]
        );

        $ironHouseTrainer = User::query()->updateOrCreate(
            ['email' => 'trainer@ironhouse.test'],
            [
                'name' => 'Iron House Trainer',
                'password' => Hash::make('trainer1234'),
                'email_verified_at' => now(),
                'is_admin_support' => false,
                'is_super_admin' => false,
            ]
        );

        $ironHouse = Club::query()->updateOrCreate(
            ['slug' => 'iron-house-club'],
            [
                'name' => 'Iron House Club',
                'description' => 'Democlub fuer Manager- und Trainer-Ansicht.',
                'billing_company_name' => 'Iron House Club',
                'billing_contact_name' => 'Iron House Manager',
                'billing_email' => 'billing@ironhouse.test',
                'billing_address_line1' => 'Kampfring 7',
                'billing_address_line2' => '2. Etage',
                'billing_zip' => '50667',
                'billing_city' => 'Koeln',
                'billing_country' => 'DE',
                'created_by_user_id' => $ironHouseManager->getKey(),
            ]
        );

        DB::table('club_user')->updateOrInsert(
            [
                'club_id' => $club->getKey(),
                'user_id' => $admin->getKey(),
            ],
            [
                'role' => 'manager',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $blueCorner->users()->syncWithoutDetaching([
            $blueCornerManager->getKey() => ['role' => 'manager', 'joined_at' => now()],
            $blueCornerTrainer1->getKey() => ['role' => 'trainer', 'joined_at' => now()],
            $blueCornerTrainer2->getKey() => ['role' => 'trainer', 'joined_at' => now()],
        ]);

        $ironHouse->users()->syncWithoutDetaching([
            $ironHouseManager->getKey() => ['role' => 'manager', 'joined_at' => now()],
            $ironHouseTrainer->getKey() => ['role' => 'trainer', 'joined_at' => now()],
        ]);

        Fighter::query()->updateOrCreate(
            ['club_id' => $blueCorner->getKey(), 'first_name' => 'Max', 'last_name' => 'Mittelgewicht'],
            [
                'created_by_user_id' => $blueCornerTrainer1->getKey(),
                'birth_date' => '1998-04-12',
                'weight_class' => 'Weltergewicht',
                'status' => 'active',
            ]
        );

        Fighter::query()->updateOrCreate(
            ['club_id' => $blueCorner->getKey(), 'first_name' => 'Lea', 'last_name' => 'Links'],
            [
                'created_by_user_id' => $blueCornerTrainer2->getKey(),
                'birth_date' => '2001-09-03',
                'weight_class' => 'Leichtgewicht',
                'status' => 'active',
            ]
        );

        Fighter::query()->updateOrCreate(
            ['club_id' => $ironHouse->getKey(), 'first_name' => 'Tom', 'last_name' => 'Schwer'],
            [
                'created_by_user_id' => $ironHouseTrainer->getKey(),
                'birth_date' => '1995-01-20',
                'weight_class' => 'Mittelgewicht',
                'status' => 'active',
            ]
        );

        $demoClubBlueprints = [
            [
                'club' => [
                    'slug' => 'spree-warriors-berlin',
                    'name' => 'Spree Warriors Berlin',
                    'description' => 'Democlub fuer Event-Meldungen und Teamverwaltung.',
                    'billing_company_name' => 'Spree Warriors Berlin e.V.',
                    'billing_contact_name' => 'Marlon Becker',
                    'billing_email' => 'billing@spree-warriors.test',
                    'billing_address_line1' => 'Spreering 8',
                    'billing_address_line2' => null,
                    'billing_zip' => '10243',
                    'billing_city' => 'Berlin',
                    'billing_country' => 'DE',
                ],
                'users' => [
                    ['name' => 'Marlon Becker', 'email' => 'manager@spree-warriors.test', 'password' => 'manager1234', 'role' => 'manager'],
                    ['name' => 'Kira Feld', 'email' => 'trainer1@spree-warriors.test', 'password' => 'trainer1234', 'role' => 'trainer'],
                    ['name' => 'Nico Sturm', 'email' => 'trainer2@spree-warriors.test', 'password' => 'trainer1234', 'role' => 'trainer'],
                ],
                'fighters' => [
                    ['first_name' => 'Aylin', 'last_name' => 'Kaya', 'birth_date' => '2005-08-18', 'weight' => 50.8, 'wins' => 1, 'losses' => 0, 'draws' => 0],
                    ['first_name' => 'Ruben', 'last_name' => 'Kraft', 'birth_date' => '2001-03-04', 'weight' => 63.7, 'wins' => 4, 'losses' => 1, 'draws' => 0],
                    ['first_name' => 'Mina', 'last_name' => 'Tas', 'birth_date' => '1999-11-29', 'weight' => 57.9, 'wins' => 9, 'losses' => 2, 'draws' => 1],
                    ['first_name' => 'Jaro', 'last_name' => 'Ilic', 'birth_date' => '1994-06-12', 'weight' => 74.3, 'wins' => 2, 'losses' => 3, 'draws' => 0],
                ],
            ],
            [
                'club' => [
                    'slug' => 'nordlicht-boxteam',
                    'name' => 'Nordlicht Boxteam',
                    'description' => 'Democlub mit gemischten Gewichtsklassen.',
                    'billing_company_name' => 'Nordlicht Boxteam',
                    'billing_contact_name' => 'Hannah Clausen',
                    'billing_email' => 'billing@nordlicht-boxteam.test',
                    'billing_address_line1' => 'Hafenstieg 14',
                    'billing_address_line2' => null,
                    'billing_zip' => '20457',
                    'billing_city' => 'Hamburg',
                    'billing_country' => 'DE',
                ],
                'users' => [
                    ['name' => 'Hannah Clausen', 'email' => 'manager@nordlicht-boxteam.test', 'password' => 'manager1234', 'role' => 'manager'],
                    ['name' => 'Batu Kilic', 'email' => 'trainer1@nordlicht-boxteam.test', 'password' => 'trainer1234', 'role' => 'trainer'],
                    ['name' => 'Svenja Holm', 'email' => 'trainer2@nordlicht-boxteam.test', 'password' => 'trainer1234', 'role' => 'trainer'],
                ],
                'fighters' => [
                    ['first_name' => 'Lina', 'last_name' => 'Peters', 'birth_date' => '2007-02-14', 'weight' => 48.6, 'wins' => 0, 'losses' => 0, 'draws' => 0],
                    ['first_name' => 'Yusuf', 'last_name' => 'Demir', 'birth_date' => '2000-09-22', 'weight' => 67.4, 'wins' => 6, 'losses' => 2, 'draws' => 1],
                    ['first_name' => 'Paula', 'last_name' => 'Brandt', 'birth_date' => '1996-01-31', 'weight' => 59.1, 'wins' => 3, 'losses' => 4, 'draws' => 0],
                    ['first_name' => 'Iven', 'last_name' => 'Rauch', 'birth_date' => '1992-12-09', 'weight' => 80.2, 'wins' => 10, 'losses' => 3, 'draws' => 0],
                ],
            ],
        ];

        foreach ($demoClubBlueprints as $blueprint) {
            $members = [];

            foreach ($blueprint['users'] as $userData) {
                $user = User::query()->updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => Hash::make($userData['password']),
                        'email_verified_at' => now(),
                        'is_admin_support' => false,
                        'is_super_admin' => false,
                    ]
                );

                $members[] = [
                    'id' => (int) $user->getKey(),
                    'role' => $userData['role'],
                ];
            }

            $managerId = collect($members)->firstWhere('role', 'manager')['id'] ?? null;
            if (! is_int($managerId)) {
                continue;
            }

            $club = Club::query()->updateOrCreate(
                ['slug' => $blueprint['club']['slug']],
                [
                    'name' => $blueprint['club']['name'],
                    'description' => $blueprint['club']['description'],
                    'billing_company_name' => $blueprint['club']['billing_company_name'],
                    'billing_contact_name' => $blueprint['club']['billing_contact_name'],
                    'billing_email' => $blueprint['club']['billing_email'],
                    'billing_address_line1' => $blueprint['club']['billing_address_line1'],
                    'billing_address_line2' => $blueprint['club']['billing_address_line2'],
                    'billing_zip' => $blueprint['club']['billing_zip'],
                    'billing_city' => $blueprint['club']['billing_city'],
                    'billing_country' => $blueprint['club']['billing_country'],
                    'created_by_user_id' => $managerId,
                ]
            );

            $pivotData = [];
            foreach ($members as $member) {
                $pivotData[$member['id']] = ['role' => $member['role'], 'joined_at' => now()];
            }
            $club->users()->syncWithoutDetaching($pivotData);

            foreach ($blueprint['fighters'] as $index => $fighterData) {
                $creatorId = $members[$index % count($members)]['id'];

                Fighter::query()->updateOrCreate(
                    [
                        'club_id' => $club->getKey(),
                        'first_name' => $fighterData['first_name'],
                        'last_name' => $fighterData['last_name'],
                    ],
                    [
                        'created_by_user_id' => $creatorId,
                        'birth_date' => $fighterData['birth_date'],
                        'weight_class' => null,
                        'sport_modules' => ['boxing'],
                        'boxing_weight_entries' => [[
                            'date' => now()->toDateString(),
                            'weight_kg' => $fighterData['weight'],
                        ]],
                        'boxing_bout_count_entries' => [[
                            'date' => now()->toDateString(),
                            'wins' => $fighterData['wins'],
                            'losses' => $fighterData['losses'],
                            'draws' => $fighterData['draws'],
                        ]],
                        'boxing_pass_entries' => [[
                            'keyword' => 'Arzt gueltig bis',
                            'date' => now()->addMonths(6)->toDateString(),
                        ]],
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
