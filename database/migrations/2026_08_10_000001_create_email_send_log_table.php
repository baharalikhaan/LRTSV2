<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailSendLogTable extends Migration
{
    public function up()
    {
        Schema::create('email_send_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_by')->constrained('users');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('cc')->nullable();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_original_name')->nullable();
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_send_log');
    }
}
