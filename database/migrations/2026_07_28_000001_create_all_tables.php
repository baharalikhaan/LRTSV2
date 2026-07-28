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

        // Remove INSERT statements (data is not part of schema)
        $sql = preg_replace('/^INSERT\s+INTO.+?;\s*$/ism', '', $sql);

        // Remove SET/START/COMMIT statements
        $sql = preg_replace('/^(SET|START|COMMIT|ROLLBACK).*?;?\s*$/im', '', $sql);

        // Remove phpMyAdmin SQL dumping comments
        $sql = preg_replace('/^--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*!.*?\*\//s', '', $sql);

        // Remove blank lines
        $lines = array_filter(explode("\n", $sql), fn($l) => trim($l) !== '');
        $sql = implode("\n", $lines);

        if (empty(trim($sql))) {
            return;
        }

        // On a fresh database, run everything
        try {
            DB::unprepared($sql);
        } catch (\Exception $e) {
            // If tables already exist, that's expected
            if (!str_contains($e->getMessage(), 'already exists')) {
                throw $e;
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
