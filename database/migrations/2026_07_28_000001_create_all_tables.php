<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAllTables extends Migration
{
    public function up()
    {
        $sqlPath = database_path('schema/schema.sql');

        if (!file_exists($sqlPath)) {
            throw new RuntimeException('Schema file not found at: ' . $sqlPath);
        }

        $sql = file_get_contents($sqlPath);

        // Remove comments and delimiter statements
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*!.*?\*\//s', '', $sql);
        $sql = str_replace(['DELIMITER ;;', 'DELIMITER ;', ';;'], '', $sql);

        // Split by semicolons and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => !empty($s) && !str_starts_with($s, '/*')
        );

        foreach ($statements as $statement) {
            if (preg_match('/`?migrations`?/i', $statement)) continue;

            // Skip CREATE TABLE if table already exists
            if (preg_match('/create\s+table\s+`?(\w+)`?/i', $statement, $m)) {
                if (Schema::hasTable($m[1])) continue;
            }

            try {
                DB::statement($statement);
            } catch (\Exception $e) {
                // For ALTER TABLE on existing tables, errors are expected
                if (!preg_match('/create\s+table/i', $statement)) {
                    // Ignore ALTER errors on existing tables
                } else {
                    throw $e;
                }
            }
        }
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
