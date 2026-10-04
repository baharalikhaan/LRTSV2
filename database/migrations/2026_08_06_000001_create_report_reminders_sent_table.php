<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportRemindersSentTable extends Migration
{
    public function up()
    {
        Schema::create('report_reminders_sent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('column_key');
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('recipient_type', 20); // lpi, reviewer, admin
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['program_id', 'column_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_reminders_sent');
    }
}
