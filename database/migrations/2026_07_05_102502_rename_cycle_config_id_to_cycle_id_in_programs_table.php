<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCycleConfigIdToCycleIdInProgramsTable extends Migration
{
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            // Drop the old FK then rename the column
            $table->dropForeign(['cycle_config_id']);
            $table->renameColumn('cycle_config_id', 'cycle_id');
            $table->foreign('cycle_id')->references('id')->on('cycle_configs')->nullOnDelete();
        });

        // Drop the grant_type string column — it's redundant with grant_type_id FK
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('grant_type');
        });
    }

    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('grant_type', 50)->nullable();
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['cycle_id']);
            $table->renameColumn('cycle_id', 'cycle_config_id');
            $table->foreign('cycle_config_id')->references('id')->on('cycle_configs')->nullOnDelete();
        });
    }
}
