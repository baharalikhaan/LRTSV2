<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNationalityIdToUsersTable extends Migration
{
    public function up()
    {
        // Nationalities table may be missing on older production schemas
        if (!Schema::hasTable('nationalities')) {
            Schema::create('nationalities', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'nationality_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('nationality_id')->nullable()->constrained('nationalities')->nullOnDelete();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'nationality_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('nationality_id');
            });
        }
    }
}