<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Email templates were a standalone CRUD never consumed by any sender. To send
 * event-based notifications (reviewer assigned, project registered, progress
 * updated/graded, proposal decision, project imported) each system template is
 * addressed by a stable machine key. Admin-created ad-hoc templates keep a
 * null key.
 */
class AddKeyToEmailTemplates extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_templates') && !Schema::hasColumn('email_templates', 'key')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->string('key')->nullable()->unique()->after('id');
            });
        }

        $this->seedEventTemplates();
    }

    /**
     * Ensure one row exists for every system event template. Idempotent:
     * existing rows (matched by key) are left untouched so admin edits survive.
     */
    protected function seedEventTemplates(): void
    {
        if (!Schema::hasTable('email_templates') || !Schema::hasColumn('email_templates', 'key')) {
            return;
        }

        foreach (\App\Models\EmailTemplate::EVENTS as $key => $event) {
            $exists = DB::table('email_templates')->where('key', $key)->exists();
            if ($exists) {
                continue;
            }

            DB::table('email_templates')->insert([
                'key'        => $key,
                'name'       => $event['name'],
                'subject'    => $event['subject'],
                'body'       => $event['body'],
                'signature'  => null,
                'category'   => $event['category'] ?? 'notification',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('email_templates') && Schema::hasColumn('email_templates', 'key')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->dropColumn('key');
            });
        }
    }
}
