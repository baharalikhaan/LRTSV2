<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('user_pillars')) {
            Schema::create('user_pillars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('pillar_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'pillar_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_pillars');
    }
};
