<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrainingUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $applicantName;
    public $trainingTitle;
    public $trainingDescription;
    public $organizationName;
    public $schedules;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $applicantName,
        string $trainingTitle,
        string $trainingDescription,
        string $organizationName,
        array $schedules
    ) {
        $this->applicantName = $applicantName;
        $this->trainingTitle = $trainingTitle;
        $this->trainingDescription = $trainingDescription;
        $this->organizationName = $organizationName;
        $this->schedules = $schedules;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Training Details Updated - ' . $this->trainingTitle . ' - ' . $this->organizationName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.training-updated',
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

