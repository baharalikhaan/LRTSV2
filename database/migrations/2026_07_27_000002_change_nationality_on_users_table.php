<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeNationalityOnUsersTable extends Migration
{
    public function up()
    {
        // Add the foreign key column
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nationality_id')) {
                $table->foreignId('nationality_id')->nullable()->after('qu_id');
            }
        });

        // Migrate existing string nationalities to nationality_id
        $existing = DB::table('users')
            ->whereNotNull('nationality')
            ->where('nationality', '!=', '')
            ->select('id', 'nationality')
            ->get();

        foreach ($existing as $user) {
            $nat = DB::table('nationalities')->where('name', $user->nationality)->first();
            if (!$nat) {
                $natId = DB::table('nationalities')->insertGetId([
                    'name' => $user->nationality,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $natId = $nat->id;
            }
            DB::table('users')->where('id', $user->id)->update(['nationality_id' => $natId]);
        }

        // Drop the old string column
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nationality')) {
                $table->dropColumn('nationality');
            }
        });

        // Add foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('nationality_id')->references('id')->on('nationalities')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['nationality_id']);
            $table->string('nationality')->nullable()->after('qu_id');
        });

        // Restore nationality strings from nationality_id
        $users = DB::table('users')
            ->whereNotNull('nationality_id')
            ->select('id', 'nationality_id')
            ->get();

        foreach ($users as $user) {
            $nat = DB::table('nationalities')->find($user->nationality_id);
            if ($nat) {
                DB::table('users')->where('id', $user->id)->update(['nationality' => $nat->name]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nationality_id');
        });
    }
}
