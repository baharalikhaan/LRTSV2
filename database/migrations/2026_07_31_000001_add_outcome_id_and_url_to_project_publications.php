<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_publications', function (Blueprint $table) {
            $table->foreignId('outcome_id')->nullable()->after('project_id')->constrained('project_outcomes')->nullOnDelete();
            $table->string('url')->nullable()->after('doi');
        });
    }

    public function down(): void
    {
        Schema::table('project_publications', function (Blueprint $table) {
            $table->dropForeign(['outcome_id']);
            $table->dropColumn(['outcome_id', 'url']);
        });
    }
};
