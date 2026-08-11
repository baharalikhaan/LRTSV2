<?php

namespace App\Mail;

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

    public function __construct(string $columnKey, string $projectTitle, string $programTitle, string $recipientName)
    {
        $this->columnKey = $columnKey;
        $this->projectTitle = $projectTitle;
        $this->programTitle = $programTitle;
        $this->recipientName = $recipientName;
        $this->context = (new CycleProgressReportService)->getColumnEmailContext($columnKey);
    }

    public function build(): self
    {
        return $this->subject($this->context['subject'])
            ->view('emails.project-reminder');
    }
}
