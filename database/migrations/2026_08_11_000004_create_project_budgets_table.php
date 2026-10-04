<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('project_num')->nullable();
            $table->string('project_name')->nullable();
            $table->decimal('budget_amount', 12, 2)->default(0);
            $table->decimal('actual_exp_amount', 12, 2)->default(0);
            $table->decimal('commitment_amount', 12, 2)->default(0);
            $table->decimal('available_balance', 12, 2)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('project_num');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_budgets');
    }
};
