<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollegeSeeder extends Seeder
{
    public function run(): void
    {
        $colleges = [
            ['id' => 1,  'code' => 'BRC',     'name' => 'Biomedical Research Center'],
            ['id' => 2,  'code' => 'CAM',     'name' => 'College of Advanced Materials'],
            ['id' => 3,  'code' => 'CAS',     'name' => 'College of Arts and Sciences'],
            ['id' => 4,  'code' => 'CBE',     'name' => 'College of Business and Education'],
            ['id' => 5,  'code' => 'CDM',     'name' => 'College of Dental Medicine'],
            ['id' => 6,  'code' => 'CED',     'name' => 'College of Education'],
            ['id' => 7,  'code' => 'CENG',    'name' => 'College of Engineering'],
            ['id' => 8,  'code' => 'CHS',     'name' => 'College of Health Sciences'],
            ['id' => 9,  'code' => 'CLAW',    'name' => 'College of Law'],
            ['id' => 10, 'code' => 'CLU',     'name' => 'Central Laboratories Unit'],
            ['id' => 11, 'code' => 'CMED',    'name' => 'College of Medicine'],
            ['id' => 12, 'code' => 'CPH',     'name' => 'College of Pharmacy'],
            ['id' => 13, 'code' => 'CSIS',    'name' => 'College of Sharia and Islamic Studies'],
            ['id' => 14, 'code' => 'ESC',     'name' => 'Environmental Science Center'],
            ['id' => 15, 'code' => 'IK-CHSS', 'name' => 'Ibn Khaldon Center for Humanities & Social Sciences'],
            ['id' => 16, 'code' => 'LARC',    'name' => 'Laboratory of Animal Research Center'],
            ['id' => 17, 'code' => 'SESRI',   'name' => 'Social and Economic Survey Research Institute'],
            ['id' => 18, 'code' => 'YSC',     'name' => 'Youth Service Center'],
            ['id' => 19, 'code' => 'GS',      'name' => 'General Studies'],
            ['id' => 20, 'code' => 'ARS',     'name' => 'Agriculture Research Station'],
            ['id' => 21, 'code' => 'QUH',     'name' => 'QU Health'],
            ['id' => 22, 'code' => 'CNRS',    'name' => 'College of Nursing'],
            ['id' => 23, 'code' => 'CSS',     'name' => 'College of Sports Sciences'],
        ];

        foreach ($colleges as $college) {
            DB::table('colleges')->updateOrInsert(
                ['id' => $college['id']],
                array_merge($college, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
