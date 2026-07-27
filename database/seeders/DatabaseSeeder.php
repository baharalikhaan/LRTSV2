<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(NationalitiesTableSeeder::class);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@qu.edu.qa',
            'password' => bcrypt('password'),
            'type' => 'Admin',
            'is_active' => true,
        ]);
    }
}
