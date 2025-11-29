<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewSchedule extends Mailable
{
    use Queueable, SerializesModels;

    public $applicantName;
    public $positionTitle;
    public $organizationName;
    public $interviewDate;
    public $interviewMode;
    public $interviewLocation;
    public $interviewLink;
    public $customBody;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $applicantName,
        string $positionTitle,
        string $organizationName,
        string $interviewDate,
        string $interviewMode,
        ?string $interviewLocation = null,
        ?string $interviewLink = null,
        ?string $customBody = null
    ) {
        $this->applicantName = $applicantName;
        $this->positionTitle = $positionTitle;
        $this->organizationName = $organizationName;
        $this->interviewDate = $interviewDate;
        $this->interviewMode = $interviewMode;
        $this->interviewLocation = $interviewLocation;
        $this->interviewLink = $interviewLink;
        $this->customBody = $customBody;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Interview Schedule - ' . $this->positionTitle . ' - ' . $this->organizationName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.interview-schedule',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

