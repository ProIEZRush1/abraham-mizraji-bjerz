<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoConfirmadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Pedido $pedido)
    {
        $this->pedido->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Confirmamos tu compra — Pedido {$this->pedido->numero_pedido}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.pedidos.confirmado',
            with: ['pedido' => $this->pedido],
        );
    }
}
