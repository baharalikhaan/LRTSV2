#!/usr/bin/env bash
set -e

echo "=== Step 1: Dump current schema ==="
php artisan schema:dump

echo ""
echo "=== Step 2: Remove old migration files ==="
rm -f database/migrations/*.php
echo "Deleted all old migration files."

echo ""
echo "=== Step 3: Verify new migration file exists ==="
if [ -f database/migrations/2026_07_28_000001_create_all_tables.php ]; then
    echo "OK — new consolidated migration found."
else
    echo "ERROR: Consolidated migration not found! Aborting."
    exit 1
fi

echo ""
echo "=== Step 4: Update migrations table ==="
php artisan tinker --execute="DB::table('migrations')->truncate();"
php artisan migrate --force

echo ""
echo "=== Done ==="
echo "Single migration created. Old migration files removed."
echo "The dump file is at: database/schema/mysql-schema.dump"
