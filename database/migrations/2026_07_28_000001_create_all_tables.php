<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAllTables extends Migration
{
    public function up()
    {
        $dumpPath = database_path('schema/mysql-schema.dump');

        if (file_exists($dumpPath)) {
            $sql = file_get_contents($dumpPath);
            if (!empty(trim($sql))) {
                DB::unprepared($sql);
                return;
            }
        }

        // Fallback: try schema.sql
        $fallback = database_path('schema.sql');
        if (file_exists($fallback)) {
            $sql = file_get_contents($fallback);
            if (!empty(trim($sql))) {
                DB::unprepared($sql);
                return;
            }
        }

        throw new RuntimeException(
            'No schema file found. Run "php artisan schema:dump" first, ' .
            'or place a schema.sql file in the database directory.'
        );
    }

    public function down()
    {
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . config('database.connections.mysql.database');

        foreach ($tables as $table) {
            $name = $table->$key;
            if ($name === 'migrations') {
                continue;
            }
            Schema::dropIfExists($name);
        }
    }
}
