<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colleges', function (Blueprint $table) {
            if (!Schema::hasColumn('colleges', 'code')) {
                $table->string('code', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('colleges', function (Blueprint $table) {
            if (Schema::hasColumn('colleges', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};