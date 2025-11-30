<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationDeclined extends Mailable
{
    use Queueable, SerializesModels;

    public $applicantName;
    public $positionTitle;
    public $organizationName;
    public $customBody;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $applicantName,
        string $positionTitle,
        string $organizationName,
        ?string $customBody = null
    ) {
        $this->applicantName = $applicantName;
        $this->positionTitle = $positionTitle;
        $this->organizationName = $organizationName;
        $this->customBody = $customBody;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Update - ' . $this->positionTitle . ' - ' . $this->organizationName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application-declined',
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

