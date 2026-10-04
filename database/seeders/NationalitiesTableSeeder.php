<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NationalitiesTableSeeder extends Seeder
{
    public function run()
    {
        $nationalities = [
            'Bangladeshi',
            'Egyptian',
            'Indian',
            'Iranian',
            'Iraqi',
            'Jordanian',
            'Kuwaiti',
            'Lebanese',
            'Moroccan',
            'Omani',
            'Pakistani',
            'Palestinian',
            'Qatari',
            'Saudi',
            'Somali',
            'Sudanese',
            'Syrian',
            'Tunisian',
            'Turkish',
            'Yemeni',
            'Algerian',
            'Bahraini',
            'British',
            'Canadian',
            'Emirati',
            'Filipino',
            'Indonesian',
            'Malaysian',
            'Nigerian',
            'American',
        ];

        foreach ($nationalities as $name) {
            DB::table('nationalities')->insertOrIgnore([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
