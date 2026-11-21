<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Editable e-mail for a forwarded rejection of type "Other".
 *
 * An "other" rejection is neither a resubmission nor an ethics-document
 * request, but the admin review step used to send the ethical-approval mail for
 * it. It now has its own template (key: rejection_other) so the wording can be
 * edited from Admin -> Email Templates, with these placeholders available on
 * top of the usual project ones:
 *
 *   *report_label*, *rejection_type*, *admin_message*
 */
class AddOtherRejectionEmailTemplate extends Migration
{
    private const KEY = 'rejection_other';

    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        // Never overwrite wording that has already been edited in the admin UI.
        if (DB::table('email_templates')->where('key', self::KEY)->exists()) {
            return;
        }

        DB::table('email_templates')->insert([
            'key'        => self::KEY,
            'name'       => 'Report Rejection — Other (LPI)',
            'subject'    => '*report_label* Clarification Required — *old_project_id* (*program_title*)',
            'body'       => "Following the research office's review of the reviewer's decision on your *report_label*, further clarification is required before the report can be evaluated again.\n\nReason (*rejection_type*): *admin_message*",
            'signature'  => null,
            'category'   => 'notification',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('email_templates')) {
            DB::table('email_templates')->where('key', self::KEY)->delete();
        }
    }
}
