<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrantSeeder extends Seeder
{
    public function run(): void
    {
        $grants = [
            [
                'id' => 1,
                'grant_code' => 'QUHI',
                'grant_name' => 'Qatar University High Impact Grant',
                'category' => 'regular',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 3,
                'description' => 'High-impact research funding for Qatar University faculty.',
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'grant_code' => 'QUST',
                'grant_name' => 'Qatar University Student Grant',
                'category' => 'regular',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 1,
                'description' => 'Research grant program for undergraduate and graduate students.',
                'is_active' => 1,
            ],
            [
                'id' => 3,
                'grant_code' => 'IRCC',
                'grant_name' => 'International Research Collaboration Co-Fund',
                'category' => 'regular',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 2,
                'description' => 'Co-funding scheme to support international research collaborations.',
                'is_active' => 1,
            ],
            [
                'id' => 4,
                'grant_code' => 'CDIRCC',
                'grant_name' => 'Concept Development-International Research Collaboration Co-Fund',
                'category' => 'regular',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 1,
                'description' => 'Concept development phase for international research collaboration co-funding.',
                'is_active' => 1,
            ],
            [
                'id' => 5,
                'grant_code' => 'QUCP',
                'grant_name' => 'National Capacity Building Program',
                'category' => 'regular',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 2,
                'description' => 'Capacity building program for national research development.',
                'is_active' => 1,
            ],
            [
                'id' => 6,
                'grant_code' => 'QUCG',
                'grant_name' => 'Qatar University Collaborative Grant',
                'category' => 'regular',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 2,
                'description' => 'Collaborative research grant program for multi-disciplinary teams.',
                'is_active' => 1,
            ],
            [
                'id' => 7,
                'grant_code' => 'QUT2RP',
                'grant_name' => 'Transformative Research Priorities Readiness Program',
                'category' => 'regular',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 2,
                'description' => 'Readiness program targeting transformative research priorities.',
                'is_active' => 1,
            ],
            [
                'id' => 8,
                'grant_code' => 'QUIHS',
                'grant_name' => 'Interdisciplinary Humanities Grant',
                'category' => 'student',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 2,
                'description' => 'Research funding for interdisciplinary humanities projects.',
                'is_active' => 1,
            ],
            [
                'id' => 9,
                'grant_code' => 'CG',
                'grant_name' => 'Conference Grant',
                'category' => 'regular',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 1,
                'description' => 'Funding support for attending and presenting at academic conferences.',
                'is_active' => 1,
            ],
            [
                'id' => 10,
                'grant_code' => 'NRPU',
                'grant_name' => 'National Research Program for Universities',
                'category' => 'regular',
                'funding_agency' => 'Qatar Research Development and Innovation Council',
                'max_duration_years' => 3,
                'description' => 'National-level research funding program for Qatari universities.',
                'is_active' => 1,
            ],
            [
                'id' => 11,
                'grant_code' => 'SRGP',
                'grant_name' => 'Startup Research Grant Program',
                'category' => 'regular',
                'funding_agency' => 'Qatar University',
                'max_duration_years' => 2,
                'description' => 'Seed funding for new research initiatives and early-stage projects.',
                'is_active' => 1,
            ],
            [
                'id' => 12,
                'grant_code' => 'TDF',
                'grant_name' => 'Technology Development Fund',
                'category' => 'regular',
                'funding_agency' => 'Qatar Research Development and Innovation Council',
                'max_duration_years' => 2,
                'description' => 'Funding for technology development and commercialization projects.',
                'is_active' => 1,
            ],
        ];

        foreach ($grants as $grant) {
            DB::table('grants')->updateOrInsert(
                ['id' => $grant['id']],
                array_merge($grant, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
