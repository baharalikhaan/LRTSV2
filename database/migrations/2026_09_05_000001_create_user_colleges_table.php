<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('user_colleges')) {
            Schema::create('user_colleges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('college_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'college_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_colleges');
    }
};
