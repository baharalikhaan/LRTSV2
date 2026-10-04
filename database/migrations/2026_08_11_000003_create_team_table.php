<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('team')) {
            Schema::create('team', function (Blueprint $table) {
                $table->id();
                $table->string('path')->nullable();
                $table->string('name');
                $table->string('role')->nullable();
                $table->text('introduction')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team');
    }
};
