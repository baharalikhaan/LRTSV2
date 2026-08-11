<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class GenericEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    public string $body;
    public string $senderName;
    public string $recipientName;
    public ?string $attachmentPath;
    public ?string $attachmentName;

    public function __construct(string $subject, string $body, string $senderName, string $recipientName, ?string $attachmentPath = null, ?string $attachmentName = null)
    {
        $this->subjectLine = $subject;
        $this->body = $body;
        $this->senderName = $senderName;
        $this->recipientName = $recipientName;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
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
