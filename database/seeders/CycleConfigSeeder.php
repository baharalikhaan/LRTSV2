<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CycleConfigSeeder extends Seeder
{
    public function run(): void
    {
        $cycles = [
            ['id' => 1, 'year' => 2019, 'title' => 'Cycle-2019'],
            ['id' => 2, 'year' => 2020, 'title' => 'Cycle-2020'],
            ['id' => 3, 'year' => 2021, 'title' => 'Cycle-2021'],
            ['id' => 4, 'year' => 2022, 'title' => 'Cycle-2022'],
            ['id' => 5, 'year' => 2023, 'title' => 'Cycle-2023'],
            ['id' => 6, 'year' => 2024, 'title' => 'Cycle-2024'],
            ['id' => 7, 'year' => 2025, 'title' => 'Cycle-2025'],
            ['id' => 8, 'year' => 2026, 'title' => 'Cycle-2026'],
        ];

        foreach ($cycles as $cycle) {
            DB::table('cycle_configs')->updateOrInsert(
                ['id' => $cycle['id']],
                array_merge($cycle, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
