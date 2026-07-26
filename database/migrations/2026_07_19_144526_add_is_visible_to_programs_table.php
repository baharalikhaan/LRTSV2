<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsVisibleToProgramsTable extends Migration
{
    public function up()
    {
        Schema::table('programs', function (Blueprint $table) {
            if (!Schema::hasColumn('programs', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('description');
            }
        });
    }

    public function down()
    {
        Schema::table('programs', function (Blueprint $table) {
            if (Schema::hasColumn('programs', 'is_visible')) {
                $table->dropColumn('is_visible');
            }
        });
    }
}
