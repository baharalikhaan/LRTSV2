<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropGrantTypeIdFromProgramsTable extends Migration
{
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['grant_type_id']);
            $table->dropColumn('grant_type_id');
        });
    }

    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('grant_type_id')->nullable()->after('grant_id')->constrained('grant_types')->nullOnDelete();
        });
    }
}
