<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Legacy import rows in project_submissions were inserted without
     * created_at (only updated_at was touched). The Update Progress page
     * renders $submission->created_at->format(...) and crashed with
     * "Call to a member function format() on null".
     */
    public function up(): void
    {
        DB::table('project_submissions')
            ->whereNull('created_at')
            ->whereNotNull('updated_at')
            ->update(['created_at' => DB::raw('updated_at')]);

        // Any row with both null (none locally, guard anyway): use NOW().
        DB::table('project_submissions')
            ->whereNull('created_at')
            ->update(['created_at' => DB::raw('NOW()')]);
    }

    public function down(): void
    {
        // Data backfill - irreversible by design.
    }
};
