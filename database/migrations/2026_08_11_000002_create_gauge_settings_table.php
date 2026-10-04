<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gauge_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('redfrom')->default(0);
            $table->integer('redto')->default(33);
            $table->integer('yellowfrom')->default(34);
            $table->integer('yellowto')->default(66);
            $table->integer('greenfrom')->default(67);
            $table->integer('greento')->default(100);
            $table->timestamps();
        });

        // Seed the 3 default gauge records
        DB::table('gauge_settings')->insert([
            ['name' => 'LPI Outcome', 'redfrom' => 0, 'redto' => 33, 'yellowfrom' => 34, 'yellowto' => 66, 'greenfrom' => 67, 'greento' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'LPI Average Score', 'redfrom' => 0, 'redto' => 33, 'yellowfrom' => 34, 'yellowto' => 66, 'greenfrom' => 67, 'greento' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Reviewer Grading', 'redfrom' => 0, 'redto' => 33, 'yellowfrom' => 34, 'yellowto' => 66, 'greenfrom' => 67, 'greento' => 100, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gauge_settings');
    }
};
