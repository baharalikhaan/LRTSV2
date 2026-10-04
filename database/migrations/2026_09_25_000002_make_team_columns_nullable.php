<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team', function (Blueprint $table) {
            $table->string('path')->nullable()->change();
            $table->string('role')->nullable()->change();
            $table->text('introduction')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('team', function (Blueprint $table) {
            $table->string('path')->change();
            $table->string('role')->change();
            $table->text('introduction')->change();
        });
    }
};