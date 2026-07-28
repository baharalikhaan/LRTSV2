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

        // Clean the SQL: remove comments, SET statements, data
        $sql = preg_replace('/^--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*!.*?\*\//s', '', $sql);
        $sql = preg_replace('/^SET\s+.*?;$/im', '', $sql);
        $sql = preg_replace('/^START TRANSACTION;$/im', '', $sql);
        $sql = preg_replace('/^COMMIT;$/im', '', $sql);

        // Remove INSERT INTO statements (data, not schema)
        $sql = preg_replace('/INSERT\s+INTO\s+.*?;\s*\n/is', '', $sql);

        $statements = $this->splitStatements($sql);

        foreach ($statements as $statement) {
            $stmt = trim($statement);
            if (empty($stmt)) continue;

            // Skip migrations table (Laravel manages it)
            if (preg_match('/`?migrations`?/i', $stmt)) continue;

            try {
                DB::statement($stmt);
            } catch (\Exception $e) {
                // On existing databases, errors like "already exists" or
                // "Duplicate" are expected — ignore them
                $msg = $e->getMessage();
                if (
                    str_contains($msg, 'already exists') ||
                    str_contains($msg, 'Duplicate') ||
                    str_contains($msg, 'Duplicate key') ||
                    str_contains($msg, 'multiple primary key') ||
                    str_contains($msg, 'Canonical key') ||
                    str_contains($msg, 'FOREIGN KEY constraint')
                ) {
                    continue;
                }
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
            if ($name === 'migrations') continue;
            Schema::dropIfExists($name);
        }
    }

    private function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $len = strlen($sql);
        $i = 0;
        $inString = false;
        $stringChar = '';

        while ($i < $len) {
            $char = $sql[$i];

            // Handle string literals
            if (!$inString && ($char === "'" || $char === '"')) {
                $inString = true;
                $stringChar = $char;
                $current .= $char;
                $i++;
            } elseif ($inString && $char === $stringChar && ($i === 0 || $sql[$i - 1] !== '\\')) {
                $inString = false;
                $current .= $char;
                $i++;
            } else {
                $current .= $char;
                if (!$inString && $char === ';' && !preg_match('/^\s*$/', $current)) {
                    $statements[] = trim($current);
                    $current = '';
                }
                $i++;
            }
        }

        $leftover = trim($current);
        if (!empty($leftover)) {
            $statements[] = $leftover;
        }

        return $statements;
    }
}