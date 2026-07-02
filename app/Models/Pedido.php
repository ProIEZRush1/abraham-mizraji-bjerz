<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    public const ESTADOS = ['pendiente', 'pagado', 'en_preparacion', 'enviado', 'entregado', 'cancelado'];

    protected $fillable = [
        'numero_pedido', 'user_id', 'direccion_id', 'zona_envio_id', 'cupon_id', 'cupon_codigo',
        'envio_destinatario', 'envio_calle', 'envio_numero', 'envio_piso_depto', 'envio_ciudad',
        'envio_provincia', 'envio_codigo_postal', 'envio_telefono',
        'subtotal', 'descuento', 'costo_envio', 'total', 'estado', 'metodo_pago',
        'mp_preference_id', 'mp_payment_id', 'pagado_at', 'notas',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'costo_envio' => 'decimal:2',
            'total' => 'decimal:2',
            'pagado_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class);
    }

    public function zonaEnvio(): BelongsTo
    {
        return $this->belongsTo(ZonaEnvio::class, 'zona_envio_id');
    }

    public function cupon(): BelongsTo
    {
        return $this->belongsTo(Cupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class);
    }

    public static function generarNumero(): string
    {
        $ultimo = static::max('id') ?? 0;

        return 'PED-'.str_pad((string) ($ultimo + 1), 6, '0', STR_PAD_LEFT);
    }
}
