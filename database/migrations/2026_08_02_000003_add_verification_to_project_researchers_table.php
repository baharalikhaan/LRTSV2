<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('project_researchers', 'verifcation_by_reviewer')) {
            Schema::table('project_researchers', function (Blueprint $table) {
                $table->string('verifcation_by_reviewer', 50)->default('pending')->after('score');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('project_researchers', 'verifcation_by_reviewer')) {
            Schema::table('project_researchers', function (Blueprint $table) {
                $table->dropColumn('verifcation_by_reviewer');
            });
        }
    }
};
