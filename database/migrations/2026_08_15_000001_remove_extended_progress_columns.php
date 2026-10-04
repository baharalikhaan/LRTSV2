<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'extended_prog_rpt_deadline',
                'extended_prog_rpt2_deadline',
                'extended_final_rpt_deadline',
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['is_extended']);
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->timestamp('extended_prog_rpt_deadline')->nullable()->after('prog_rpt_deadline');
            $table->date('extended_prog_rpt2_deadline')->nullable()->after('prog_rpt2_deadline');
            $table->timestamp('extended_final_rpt_deadline')->nullable()->after('final_rpt_deadline');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_extended')->default(false);
        });
    }
};
