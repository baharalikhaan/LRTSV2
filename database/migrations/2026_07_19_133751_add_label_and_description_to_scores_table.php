<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabelAndDescriptionToScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scores', function (Blueprint $table) {
            if (!Schema::hasColumn('scores', 'label')) {
                $table->string('label', 20)->nullable()->after('name');
            }
            if (!Schema::hasColumn('scores', 'description')) {
                $table->text('description')->nullable()->after('value');
            }
            if (!Schema::hasColumn('scores', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scores', function (Blueprint $table) {
            $cols = ['label', 'description', 'is_active'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('scores', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
