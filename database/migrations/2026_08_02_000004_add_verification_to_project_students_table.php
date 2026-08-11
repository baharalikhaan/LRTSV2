<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_students', function (Blueprint $table) {
            if (!Schema::hasColumn('project_students', 'verifcation_by_system')) {
                $table->string('verifcation_by_system', 50)->default('pending')->after('score');
            }
            if (!Schema::hasColumn('project_students', 'verifcation_by_reviewer')) {
                $table->string('verifcation_by_reviewer', 50)->default('pending')->after('verifcation_by_system');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_students', function (Blueprint $table) {
            $table->dropColumn(['verifcation_by_system', 'verifcation_by_reviewer']);
        });
    }
};
