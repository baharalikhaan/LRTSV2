<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Raw SQL: DBAL cannot introspect MariaDB `enum` columns for ->change().
        DB::statement("ALTER TABLE project_submissions MODIFY type ENUM('progress', 'final', 'readiness', 'progress2') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE project_submissions MODIFY type ENUM('progress', 'final', 'readiness') NOT NULL");
    }
};