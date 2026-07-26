<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReport2DeadlinesToProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->date('prog_rpt2_deadline')->nullable()->after('extended_prog_rpt_deadline');
            $table->date('extended_prog_rpt2_deadline')->nullable()->after('prog_rpt2_deadline');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['prog_rpt2_deadline', 'extended_prog_rpt2_deadline']);
        });
    }
}
