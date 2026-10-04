<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('projects', 'extended_progress')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('extended_progress')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('projects', 'extended_progress')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('extended_progress');
            });
        }
    }
};