<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class welcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;  // Subject property
    public $body;     // Body property
    public $fromEmail; // From email property

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $body, $fromEmail)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->fromEmail = $fromEmail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->from($this->fromEmail)
            ->subject($this->subject)
            ->view('email.welcome', ['body' => $this->body]);
    }
}
