<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Confirmación de pedido con las instrucciones para pagar por Yape
 */
class OrderPlaced extends Mailable
{
    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Recibimos tu pedido {$this->order->reference_number} - TEZAN");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-placed');
    }
}
