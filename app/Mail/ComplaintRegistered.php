<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Copia de la hoja de reclamación para el consumidor
 */
class ComplaintRegistered extends Mailable
{
    public function __construct(public Complaint $complaint)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Copia de tu hoja de reclamación {$this->complaint->code} - TEZAN");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.complaint-registered');
    }
}
