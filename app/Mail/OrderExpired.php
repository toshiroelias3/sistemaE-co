<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Aviso al cliente de que su pedido se canceló por no pagarse a tiempo
 */
class OrderExpired extends Mailable
{
    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Tu pedido {$this->order->reference_number} fue cancelado - TEZAN");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-expired');
    }
}
