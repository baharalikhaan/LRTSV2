<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    /** Support address shown in the notification emails. */
    public const SUPPORT_EMAIL = 'ros-submissions@qu.edu.qa';

    /**
     * Project detail rows that reviewer-facing emails leave out — the grant,
     * the cycle year and the report deadlines belong to the LPI.
     */
    public const REVIEWER_HIDDEN_ROWS = ['grant_name', 'cycle_year', 'deadlines'];

    /** Off-campus access notice shown at the end of the notification emails. */
    public const DISCLAIMER = 'The RTS system is accessible only through the Qatar University (QU) network, either while on campus or remotely via the GlobalProtect VPN.';

    public string $subjectLine;
    public string $body;
    public string $senderName;
    public string $recipientName;
    public ?string $attachmentPath;
    public ?string $attachmentName;

    /** Short academic heading shown at the top of the email. */
    public ?string $heading;

    /** Project information rendered as a details table (null = generic email). */
    public ?array $project;

    /** Optional "what is required" bullet points. */
    public array $guidance;

    /** Optional "action required" steps (rendered after the project details). */
    public array $actionPoints;

    /** Optional disclaimer shown in a grey box at the end of the email. */
    public ?string $disclaimer;

    /** Optional support address shown above the disclaimer. */
    public ?string $supportEmail;

    public ?string $actionUrl;
    public ?string $actionLabel;

    public function __construct(
        string $subject,
        string $body,
        string $senderName,
        string $recipientName,
        ?string $attachmentPath = null,
        ?string $attachmentName = null,
        array $options = []
    ) {
        $this->subjectLine = $subject;
        $this->body = $body;
        $this->senderName = $senderName;
        $this->recipientName = $recipientName;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;

        $this->heading = $options['heading'] ?? null;
        $this->project = $options['project'] ?? null;
        $this->guidance = $options['guidance'] ?? [];
        $this->actionPoints = $options['action_points'] ?? [];
        $this->disclaimer = $options['disclaimer'] ?? null;
        $this->supportEmail = $options['support_email'] ?? null;
        $this->actionLabel = $options['action_label'] ?? null;
        $this->actionUrl = $options['action_url']
            ?? ($this->project['url'] ?? null);
    }

    /**
     * Build the project details block used by the notification emails.
     *
     * @param  array<int, string>  $except  Detail rows to leave out
     * @return array{old_project_id:mixed,title:?string,program_title:?string,grant_name:?string,cycle_year:?string,deadlines:array,url:string}
     */
    public static function projectContext(Project $project, array $except = []): array
    {
        $program = $project->program;
        $grant   = $program ? $program->grant : null;
        $cycle   = $program ? ($program->cycle ?: $program->cycleConfig) : null;
        $deadlines = [];

        $map = [
            'prog_rpt_deadline'  => 'Progress Report 1',
            'prog_rpt2_deadline' => 'Progress Report 2',
            'final_rpt_deadline' => 'Final report',
        ];

        foreach ($map as $field => $label) {
            if ($program && $program->{$field}) {
                $due = \Illuminate\Support\Carbon::parse($program->{$field});
                $deadlines[] = [
                    'label'     => $label,
                    'date'      => $due->format('d M Y'),
                    'days_left' => now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false),
                ];
            }
        }

        // Grant name, falling back to its code when the name is not set.
        $grantName = null;
        if ($grant) {
            $name = $grant->grant_name ?: $grant->grant_code;
            $grantName = $grant->grant_code && $grant->grant_name !== $grant->grant_code
                ? $name . ' (' . $grant->grant_code . ')'
                : $name;
        }

        $context = [
            'old_project_id' => $project->old_project_id ?: $project->id,
            'title'          => $project->title,
            'program_title'  => $program->program_title ?? null,
            'grant_name'     => $grantName,
            'cycle_year'     => $cycle ? ($cycle->year ?? $cycle->title ?? null) : null,
            'deadlines'      => $deadlines,
            'url'            => route('projects.show', $project->id),
        ];

        foreach ($except as $row) {
            unset($context[$row]);
        }

        return $context;
    }

    public function build(): self
    {
        $mail = $this->subject($this->subjectLine)
            ->view('emails.generic-email');

        if ($this->attachmentPath && file_exists(storage_path('app/' . $this->attachmentPath))) {
            $mail->attach(storage_path('app/' . $this->attachmentPath), [
                'as' => $this->attachmentName ?? basename($this->attachmentPath),
            ]);
        }

        return $mail;
    }
}
