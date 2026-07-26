<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToTagsTable extends Migration
{
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            // The current tags table has: id, tag, created_at, updated_at
            // We need to add: name (copy from tag), type, description, is_active
            $table->string('name', 255)->nullable()->after('id');
            $table->string('type', 100)->nullable()->after('name');
            $table->text('description')->nullable()->after('type');
            $table->boolean('is_active')->default(true)->after('description');
        });

        // Copy existing 'tag' values into 'name'
        DB::statement('UPDATE tags SET name = tag');
    }

    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn(['name', 'type', 'description', 'is_active']);
        });
    }
}
