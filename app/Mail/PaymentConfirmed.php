<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Aviso al cliente de que su Yape fue validado y el pedido se prepara
 */
class PaymentConfirmed extends Mailable
{
    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Pago confirmado: pedido {$this->order->reference_number} - TEZAN");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-confirmed');
    }
}
