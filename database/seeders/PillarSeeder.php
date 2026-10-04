<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PillarSeeder extends Seeder
{
    /**
     * Seeds the canonical seven research pillars.
     *
     * Name-keyed create-if-missing only — existing rows (including their
     * sub-pillar content and user/project assignments) are never touched,
     * so this is safe to run on both fresh and live databases. The
     * 2026_09_26_000001_merge_duplicate_pillars migration consolidates any
     * historical duplicate pillars first.
     */
    public function run(): void
    {
        $pillars = [
            [
                'pillar' => 'Digital Technology',
                'subpillar' => 'Digital Technology',
            ],
            [
                'pillar' => 'Energy and Environment',
                'subpillar' => "Core Research Priority: Oil and Gas\nCore Research Priority: Energy Efficiency and Renewable Energy\nCore Research Priority: Materials\nCore Research Priority: Water and Food Security\nCore Research Priority: Environment and Biodiversity\nTransformative Research Priority: Waste to Value Solutions for Food, Water and Energy Sectors\nTransformative Research Priority: Cost effective CO2 capture and storage technology\nTransformative Research Priority: Combining high-performance materials and ICT for energy conversion, storage and transport\nTransformative Research Priority: Agriculture Technologies for Food and Other Applications\nEnergy and Environment",
            ],
            [
                'pillar' => 'Engineering',
                'subpillar' => "Engineering\nArchitecture - Building Design\nStructural Engineering- Materials\nChemical Sensing (Chemical Engineering)\nCivil Engineering\nArchitecture and Urban planning\nStructural Engineering",
            ],
            [
                'pillar' => 'Health and Biomedical Sciences',
                'subpillar' => "Core Research Priority: Diabetes and Cardiovascular Diseases\nCore Research Priority: Cancer\nCore Research Priority: Infectious Diseases\nCore Research Priority: Neurological, Psychiatric Disorders, and Mental Health\nCore Research Priority: Respiratory Diseases\nTransformative Research Priority: Stem Cells, Tissue Engineering and AI for Body Organs Repair\nTransformative Research Priority: Novel Techniques and Approaches in the Diagnosis and Treatment of Priority Diseases\nHealth and Biomedical Sciences",
            ],
            [
                'pillar' => 'Information and Communication Technologies',
                'subpillar' => "Core Research Priority: Telecommunications and Infrastructure\nCore Research Priority: Artificial Intelligence and Smart Systems\nCore Research Priority: ICT in Health and Biomedical Applications\nTransformative Research Priority: Advanced ICT Tools\nTransformative Research Priority: Self-defending cybersecurity architecture\nTransformative Research Priority: Blockchain Based Efficiencies\nInformation and Communication Technologies\nICT",
            ],
            [
                'pillar' => 'Resource Sustainability',
                'subpillar' => 'Resource Sustainability',
            ],
            [
                'pillar' => 'Social Sciences and Humanities',
                'subpillar' => "Core Research Priority: Economic Diversification and Sustainable Development\nCore Research Priority: Social Change and Identity\nCore Research Priority: National Security\nCore Research Priorities: Education and Capacity Building\nCore Research Priority: Women and Family\nTransformative Research Priority: Human Security\nTransformative Research Priority: Education and Economic Sustainability\nTransformative Research Priority: Entrepreneurial Strategies and Business Models\nSocial Sciences and Humanities\nSocial Sciences",
            ],
        ];

        foreach ($pillars as $pillar) {
            $exists = DB::table('pillars')->where('pillar', $pillar['pillar'])->exists();
            if (!$exists) {
                DB::table('pillars')->insert(array_merge($pillar, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
