<?php
// Run this from your project root: php database/dump-schema.php
// It generates the correct schema directly from your database.

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = DB::select('SHOW TABLES');
$key = 'Tables_in_' . env('DB_DATABASE', 'lrts_mcp');

$output = "<?php\n\n";
$output .= "use Illuminate\\Database\\Migrations\\Migration;\n";
$output .= "use Illuminate\\Database\\Schema\\Blueprint;\n";
$output .= "use Illuminate\\Support\\Facades\\Schema;\n";
$output .= "use Illuminate\\Support\\Facades\\DB;\n\n";
$output .= "class CreateAllTables extends Migration\n{\n";
$output .= "    public function up()\n    {\n";

foreach ($tables as $table) {
    $name = $table->$key;
    if ($name === 'migrations') continue;

    $createSql = DB::select("SHOW CREATE TABLE `{$name}`");
    $createStmt = $createSql[0]->{'Create Table'};

    // Convert CREATE TABLE to Schema::create()
    $output .= "        Schema::create('{$name}', function (Blueprint \$table) {\n";

    // Parse columns from SHOW COLUMNS
    $columns = DB::select("SHOW COLUMNS FROM `{$name}`");
    foreach ($columns as $col) {
        $type = $col->Type;
        $field = $col->Field;
        $nullable = $col->Null === 'YES';
        $default = $col->Default;
        $extra = $col->Extra;
        $key_info = $col->Key;

        // Map MySQL types to Blueprint methods
        $line = "            \$table->";

        if (preg_match('/^bigint\((\d+)\)\s+unsigned$/i', $type, $m)) {
            $line .= "id('{$field}')";
        } elseif (preg_match('/^bigint\((\d+)\)/i', $type)) {
            $line .= str_contains($type, 'unsigned') ? "unsignedBigInteger('{$field}')" : "bigInteger('{$field}')";
        } elseif (preg_match('/^int\((\d+)\)/i', $type)) {
            $line .= str_contains($type, 'unsigned') ? "unsignedInteger('{$field}')" : "integer('{$field}')";
        } elseif (preg_match('/^tinyint\(1\)/i', $type)) {
            $line .= "boolean('{$field}')";
        } elseif (preg_match('/^varchar\((\d+)\)/i', $type, $m)) {
            $line .= "string('{$field}', {$m[1]})";
        } elseif (preg_match('/^char\((\d+)\)/i', $type, $m)) {
            $line .= "char('{$field}', {$m[1]})";
        } elseif ($type === 'text') {
            $line .= "text('{$field}')";
        } elseif ($type === 'longtext') {
            $line .= "longText('{$field}')";
        } elseif (preg_match('/^decimal\((\d+),(\d+)\)/i', $type, $m)) {
            $line .= "decimal('{$field}', {$m[1]}, {$m[2]})";
        } elseif (preg_match('/^double\((\d+),(\d+)\)/i', $type, $m)) {
            $line .= "double('{$field}', {$m[1]}, {$m[2]})";
        } elseif (preg_match('/^float\((\d+),(\d+)\)/i', $type, $m)) {
            $line .= "float('{$field}', {$m[1]}, {$m[2]})";
        } elseif (preg_match('/^timestamp/i', $type)) {
            $line .= "timestamp('{$field}')";
        } elseif (preg_match('/^datetime/i', $type)) {
            $line .= "dateTime('{$field}')";
        } elseif (preg_match('/^date/i', $type)) {
            $line .= "date('{$field}')";
        } elseif (preg_match('/^time/i', $type)) {
            $line .= "time('{$field}')";
        } elseif (preg_match('/^json/i', $type)) {
            $line .= "json('{$field}')";
        } elseif ($type === 'tinytext') {
            $line .= "tinyText('{$field}')";
        } elseif ($type === 'mediumtext') {
            $line .= "mediumText('{$field}')";
        } else {
            $line .= "string('{$field}') // {$type}";
        }

        if ($nullable) $line .= "->nullable()";
        if ($default !== null && $default !== '') {
            $val = is_numeric($default) ? $default : "'{$default}'";
            $line .= "->default({$val})";
        }
        if (str_contains($extra, 'auto_increment')) {
            // already handled by id() or increments()
        }

        $line .= ';';
        $output .= $line . "\n";
    }

    // Handle indexes
    $indexes = DB::select("SHOW INDEX FROM `{$name}`");
    $primaryCol = null;
    $processed = [];
    foreach ($indexes as $idx) {
        if ($idx->Key_name === 'PRIMARY') {
            $primaryCol = $idx->Column_name;
            continue;
        }
        $unique = $idx->Non_unique == 0 ? '->unique()' : '';
        if ($unique && !in_array($idx->Key_name, $processed)) {
            $output .= "            \$table->index('{$idx->Column_name}'){$unique};\n";
            $processed[] = $idx->Key_name;
        }
    }

    // Handle foreign keys
    $fks = DB::select("
        SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '{$name}'
          AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $fkProcessed = [];
    foreach ($fks as $fk) {
        // Replace id column references with foreignId()->constrained()
        // This is a simplification — the actual relationship might need specific onDelete
        $output .= "            // FK: {$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}({$fk->REFERENCED_COLUMN_NAME})\n";
    }

    $output .= "        });\n\n";
}

$output .= "    }\n\n";
$output .= "    public function down()\n";
$output .= "    {\n";
$output .= "        \$tables = DB::select('SHOW TABLES');\n";
$output .= "        \$key = 'Tables_in_' . config('database.connections.mysql.database');\n";
$output .= "        foreach (\$tables as \$table) {\n";
$output .= "            \$name = \$table->\$key;\n";
$output .= "            if (\$name === 'migrations') continue;\n";
$output .= "            Schema::dropIfExists(\$name);\n";
$output .= "        }\n";
$output .= "    }\n";
$output .= "}\n";

$outPath = __DIR__ . '/migrations/2026_07_28_000001_create_all_tables.php';
file_put_contents($outPath, $output);
echo "Written to: {$outPath}\n";
echo "Tables found: " . count($tables) . "\n";
