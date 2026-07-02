<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoEstadoActualizadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public const ETIQUETAS = [
        'pendiente' => 'Pendiente',
        'pagado' => 'Pagado',
        'en_preparacion' => 'En preparación',
        'enviado' => 'Enviado',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    public function __construct(public Pedido $pedido) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu pedido {$this->pedido->numero_pedido} está: ".
                (self::ETIQUETAS[$this->pedido->estado] ?? $this->pedido->estado),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.pedidos.estado-actualizado',
            with: [
                'pedido' => $this->pedido,
                'etiqueta' => self::ETIQUETAS[$this->pedido->estado] ?? $this->pedido->estado,
            ],
        );
    }
}
