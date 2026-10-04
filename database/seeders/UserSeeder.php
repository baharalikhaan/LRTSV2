<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@qu.edu.qa',
                'password' => '$2y$10$4omSl6V5HjgNk2lBcKgZD.htiCarlH72IcVUadVEJpzMlBkLAFAe.',
                'type' => 'Admin',
                'faculty' => null,
                'qu_id' => null,
                'nationality_id' => null,
                'phone' => null,
                'department' => null,
                'college' => null,
                'pillars' => '',
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Reviewer',
                'email' => 'reviewer@qu.edu.qa',
                'password' => '$2y$10$4omSl6V5HjgNk2lBcKgZD.htiCarlH72IcVUadVEJpzMlBkLAFAe.',
                'type' => 'Reviewer',
                'faculty' => null,
                'qu_id' => null,
                'nationality_id' => null,
                'phone' => null,
                'department' => null,
                'college' => null,
                'pillars' => '',
                'is_active' => 1,
            ],
            [
                'id' => 3,
                'name' => 'LPI',
                'email' => 'lpi@qu.edu.qa',
                'password' => '$2y$10$4omSl6V5HjgNk2lBcKgZD.htiCarlH72IcVUadVEJpzMlBkLAFAe.',
                'type' => 'LPI+Reviewer',
                'faculty' => '0',
                'qu_id' => null,
                'nationality_id' => null,
                'phone' => null,
                'department' => null,
                'college' => 'College of Education',
                'pillars' => '',
                'is_active' => 1,
            ],
            [
                'id' => 4,
                'name' => 'Reviewer2',
                'email' => 'reviewer2@qu.edu.qa',
                'password' => '$2y$10$4omSl6V5HjgNk2lBcKgZD.htiCarlH72IcVUadVEJpzMlBkLAFAe.',
                'type' => 'Reviewer',
                'faculty' => null,
                'qu_id' => null,
                'nationality_id' => null,
                'phone' => null,
                'department' => null,
                'college' => null,
                'pillars' => '',
                'is_active' => 1,
            ],
            [
                'id' => 6,
                'name' => 'lpi@qu.edu.qa',
                'email' => '345345lpi@qu.edu.qa',
                'password' => '$2y$10$8lB0YArEWFYUfbwhOQLx2eJN82oAkW2yo54tEtFQj6dsiRO9m2ApC',
                'type' => 'LPI',
                'faculty' => null,
                'qu_id' => null,
                'nationality_id' => null,
                'phone' => null,
                'department' => null,
                'college' => null,
                'pillars' => null,
                'is_active' => 1,
            ],
            [
                'id' => 9,
                'name' => 'Mock SAML Admin',
                'email' => 'admin@example.com',
                'password' => '$2y$10$4omSl6V5HjgNk2lBcKgZD.htiCarlH72IcVUadVEJpzMlBkLAFAe.',
                'type' => 'Admin',
                'faculty' => '1',
                'qu_id' => null,
                'nationality_id' => 30,
                'phone' => null,
                'department' => null,
                'college' => 'College of Education',
                'pillars' => '',
                'is_active' => 1,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['id' => $user['id']],
                array_merge($user, [
                    'email_verified_at' => null,
                    'remember_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
