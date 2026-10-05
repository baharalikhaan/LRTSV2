<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'subject',
        'body',
        'signature',
        'category',
    ];

    /**
     * System event templates, addressed by a stable machine key. Each entry
     * describes one notification the app sends automatically.
     */
    public const EVENTS = [
        'reviewer_assigned' => [
            'name'     => 'Reviewer Assigned',
            'category' => 'notification',
            'subject'  => 'Project Assigned to You: *old_project_id*',
            'body'     => "Dear *name*,\n\nThe following project has been assigned to you for review:\n\nProject ID: *old_project_id*\nProject Title: *project_title*\nResearch Call: *grant_title* (*cycle*)\n\nPlease log in to RTS to review it:\n*link*",
        ],
        'proposal_accepted' => [
            'name'     => 'Proposal Accepted (LPI)',
            'category' => 'notification',
            'subject'  => 'Proposal Accepted: *old_project_id*',
            'body'     => "Dear *name*,\n\nWe are pleased to inform you that your proposal for the following project has been accepted:\n\nProject ID: *old_project_id*\nProject Title: *project_title*\n\n*link*",
        ],
        'proposal_rejected' => [
            'name'     => 'Proposal Rejected (LPI)',
            'category' => 'notification',
            'subject'  => 'Proposal Decision: *old_project_id*',
            'body'     => "Dear *name*,\n\nAfter review, your proposal for the following project was not accepted:\n\nProject ID: *old_project_id*\nProject Title: *project_title*\n\n*link*",
        ],
        'project_imported' => [
            'name'     => 'Project Added to Research Call (LPI)',
            'category' => 'notification',
            'subject'  => 'New Project Added: *old_project_id*',
            'body'     => "Dear *name*,\n\nA project has been added to the research call *grant_title* (*cycle*) under your name:\n\nProject ID: *old_project_id*\nProject Title: *project_title*\n\nPlease log in to complete the project registration:\n*link*",
        ],
        'project_registered' => [
            'name'     => 'Project Registered (LPI)',
            'category' => 'notification',
            'subject'  => 'Registration Confirmed: *old_project_id*',
            'body'     => "Dear *name*,\n\nYour project registration has been submitted successfully:\n\nProject ID: *old_project_id*\nProject Title: *project_title*\n\n*link*",
        ],
        'progress_updated' => [
            'name'     => 'Progress Report Submitted (LPI)',
            'category' => 'notification',
            'subject'  => 'Progress Report Submitted: *old_project_id*',
            'body'     => "Dear *name*,\n\nYour progress report for the following project has been submitted successfully:\n\nProject ID: *old_project_id*\nProject Title: *project_title*\n\n*link*",
        ],
        'progress_graded' => [
            'name'     => 'Progress Report Graded (LPI)',
            'category' => 'notification',
            'subject'  => 'Progress Report Graded: *old_project_id*',
            'body'     => "Dear *name*,\n\nYour progress report for the following project has been graded. You can now view the report card:\n\nProject ID: *old_project_id*\nProject Title: *project_title*\n\n*link*",
        ],
        'final_graded' => [
            'name'     => 'Final Report Graded (LPI)',
            'category' => 'notification',
            'subject'  => 'Final Report Graded: *old_project_id*',
            'body'     => "Dear *name*,\n\nYour final report for the following project has been graded. You can now view the report card:\n\nProject ID: *old_project_id*\nProject Title: *project_title*\n\n*link*",
        ],
    ];

    /**
     * All valid system template keys.
     *
     * @return string[]
     */
    public static function eventKeys(): array
    {
        return array_keys(self::EVENTS);
    }

    /**
     * Fetch a system template by its event key.
     */
    public static function findByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    /**
     * Available placeholder tags that can be used in subject/body.
     */
    public static function availablePlaceholders(): array
    {
        return [
            '*name*'           => 'Recipient name',
            '*email*'          => 'Recipient email',
            '*old_project_id*' => 'Project ID',
            '*project_title*'  => 'Project title',
            '*cycle*'          => 'Cycle year',
            '*deadline*'       => 'Deadline date',
            '*grant_title*'    => 'Grant title',
            '*link*'           => 'Action link',
        ];
    }

    /**
     * Available categories.
     */
    public static function categories(): array
    {
        return [
            'general'      => 'General',
            'reminder'     => 'Reminder',
            'notification' => 'Notification',
            'welcome'      => 'Welcome',
        ];
    }

    /**
     * Replace placeholders in the given text with provided data.
     */
    public function render(string $text, array $data = []): string
    {
        $replacements = array_merge(
            array_fill_keys(array_keys(self::availablePlaceholders()), ''),
            $data
        );

        foreach ($replacements as $key => $value) {
            $text = str_replace($key, (string) $value, $text);
        }

        return $text;
    }

    /**
     * Render the subject with placeholders.
     */
    public function renderSubject(array $data = []): string
    {
        return $this->render((string) $this->subject, $data);
    }

    /**
     * Render the body with placeholders.
     */
    public function renderBody(array $data = []): string
    {
        return $this->render((string) $this->body, $data);
    }

    /**
     * Render the signature with placeholders.
     */
    public function renderSignature(array $data = []): string
    {
        return $this->render((string) $this->signature, $data);
    }
}
