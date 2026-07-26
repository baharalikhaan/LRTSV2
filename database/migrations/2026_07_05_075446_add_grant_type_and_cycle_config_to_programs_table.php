<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGrantTypeAndCycleConfigToProgramsTable extends Migration
{
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('grant_type_id')->nullable()->after('grant_id')->constrained('grant_types')->nullOnDelete();
            $table->foreignId('cycle_config_id')->nullable()->after('grant_type_id')->constrained('cycle_configs')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['grant_type_id']);
            $table->dropForeign(['cycle_config_id']);
            $table->dropColumn(['grant_type_id', 'cycle_config_id']);
        });
    }
}
