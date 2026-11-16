<?php

namespace App\Mail;

use App\Models\Project;
use App\Services\CycleProgressReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $columnKey;
    public string $projectTitle;
    public string $programTitle;
    public string $recipientName;
    public array $context;

    /** Short academic heading shown at the top of the email body. */
    public string $heading;

    /**
     * Extra project information rendered in the email body:
     *   old_project_id, deadline (string|null), deadline_label,
     *   days_left (int|null, negative = overdue), project_url,
     *   pending (array of ['id' => …, 'title' => …])  — admin mails only
     */
    public array $details;

    public function __construct(
        string $columnKey,
        string $projectTitle,
        string $programTitle,
        string $recipientName,
        array $details = []
    ) {
        $this->columnKey = $columnKey;
        $this->projectTitle = $projectTitle;
        $this->programTitle = $programTitle;
        $this->recipientName = $recipientName;
        $this->context = (new CycleProgressReportService)->getColumnEmailContext($columnKey);
        $this->heading = $this->context['subject'];

        $this->details = array_merge([
            'old_project_id' => null,
            'deadline'       => null,
            'deadline_label' => $this->context['deadline_label'] ?? null,
            'days_left'      => null,
            'project_url'    => null,
            'pending'        => [],
        ], $details);
    }

    /**
     * Build the details array for a single project reminder.
     */
    public static function detailsForProject(Project $project, string $columnKey): array
    {
        $context = (new CycleProgressReportService)->getColumnEmailContext($columnKey);
        $program = $project->program;

        $deadline = null;
        $daysLeft = null;
        $field = $context['deadline_field'] ?? null;

        if ($program && $field && $program->{$field}) {
            $due = \Illuminate\Support\Carbon::parse($program->{$field});
            $deadline = $due->format('d M Y');
            $daysLeft = now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false);
        }

        return [
            'old_project_id' => $project->old_project_id ?: $project->id,
            'deadline'       => $deadline,
            'deadline_label' => $context['deadline_label'] ?? null,
            'days_left'      => $daysLeft,
            'project_url'    => route('projects.show', $project->id),
        ];
    }

    public function build(): self
    {
        return $this->subject($this->composeSubject())
            ->view('emails.project-reminder');
    }

    /**
     * Professional subject line that identifies the project and research call,
     * e.g. "Outcome Documentation Required — IRCC-2024-494 (IRCC – Cycle 2024)".
     * Admin mails show the number of pending projects instead of one id.
     */
    private function composeSubject(): string
    {
        $subject = $this->context['subject'];

        $id = $this->details['old_project_id'] ?? null;
        if ($id) {
            $subject .= ' — ' . $id;
        }

        $pending = $this->details['pending'] ?? [];
        if (!empty($pending)) {
            $count = count($pending);
            $subject .= ' — ' . $count . ' project' . ($count === 1 ? '' : 's');
        }

        if ($this->programTitle) {
            $subject .= ' (' . $this->programTitle . ')';
        }

        return $subject;
    }

    /**
     * Short heading shown at the top of the email body.
     */
    public function heading(): string
    {
        return $this->context['subject'];
    }
}
