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
            'subject'  => 'Reviewer Assignment Confirmed — *old_project_id* (*program_title*)',
            'body'     => "The project below has been assigned to you for evaluation. Please review the proposal and record your decision in RTS.",
        ],
        'proposal_accepted' => [
            'name'     => 'Proposal Accepted (LPI)',
            'category' => 'notification',
            'subject'  => 'Proposal Accepted — *old_project_id* (*program_title*)',
            'body'     => "We are pleased to inform you that your proposal for the project below has been accepted.\n\nPlease proceed with the project registration if you have not completed it yet.",
        ],
        'proposal_rejected' => [
            'name'     => 'Proposal Rejected (LPI)',
            'category' => 'notification',
            'subject'  => 'Proposal Decision — *old_project_id* (*program_title*)',
            'body'     => "Following the evaluation of your proposal, we regret to inform you that the project below has not been accepted.\n\nYou may contact the research office for further details on the decision.",
        ],
        'project_imported' => [
            'name'     => 'Project Added to Research Call (LPI)',
            'category' => 'notification',
            'subject'  => 'Project Added to Research Call — *old_project_id* (*program_title*)',
            'body'     => "A project has been added to *grant_title* (*cycle*) under your name.\n\nPlease complete the project registration in RTS.",
        ],
        'project_registered' => [
            'name'     => 'Project Registered (LPI)',
            'category' => 'notification',
            'subject'  => 'Registration Confirmed — *old_project_id* (*program_title*)',
            'body'     => "Your project registration has been submitted successfully.\n\nYou can now prepare the progress report for the project.",
        ],
        'progress_updated' => [
            'name'     => 'Progress Report Submitted (LPI)',
            'category' => 'notification',
            'subject'  => 'Progress Report Received — *old_project_id* (*program_title*)',
            'body'     => "Your progress report has been received.\n\nThe assigned reviewer will evaluate it and you will be notified of the outcome.",
        ],
        'progress_graded' => [
            'name'     => 'Progress Report Graded (LPI)',
            'category' => 'notification',
            'subject'  => 'Progress Report Evaluated — *old_project_id* (*program_title*)',
            'body'     => "Your progress report has been evaluated.\n\nYou can view the report card from the project page.",
        ],
        'final_graded' => [
            'name'     => 'Final Report Graded (LPI)',
            'category' => 'notification',
            'subject'  => 'Final Report Evaluated — *old_project_id* (*program_title*)',
            'body'     => "Your final report has been evaluated.\n\nYou can view the report card from the project page.",
        ],
        // Forwarded to the LPI when the admin reviews a rejection whose type is
        // "Other" — neither a resubmission nor an ethics document is expected.
        'rejection_other' => [
            'name'     => 'Report Rejection — Other (LPI)',
            'category' => 'notification',
            'subject'  => '*report_label* Clarification Required — *old_project_id* (*program_title*)',
            'body'     => "Following the research office's review of the reviewer's decision on your *report_label*, further clarification is required before the report can be evaluated again.\n\nReason (*rejection_type*): *admin_message*",
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
            '*program_title*'  => 'Research call title',
            '*cycle*'          => 'Cycle year',
            '*deadline*'       => 'Deadline date',
            '*grant_title*'    => 'Grant title',
            '*link*'           => 'Action link',
            '*report_label*'   => 'Report name (Progress Report / Progress Report 2 / Final Report)',
            '*rejection_type*' => 'Rejection type (report / ethical / other)',
            '*admin_message*'  => 'Message written by the research office',
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
