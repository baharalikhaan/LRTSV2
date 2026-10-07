<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User activity log: sign-ins (with IP / user agent / session duration) and the
 * actions a user performs. Shown to administrators from the Activity page.
 */
class CreateActivityLogsTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 30);            // login | logout | view | action
            $table->string('description')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('route')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable(); // set on logout
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
}
