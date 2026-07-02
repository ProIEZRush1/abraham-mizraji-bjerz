<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Cupon extends Model
{
    protected $table = 'cupones';

    protected $fillable = [
        'codigo', 'tipo', 'valor', 'monto_minimo', 'fecha_inicio',
        'fecha_vencimiento', 'uso_maximo', 'usos_actuales', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'monto_minimo' => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_vencimiento' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function esValidoPara(float $subtotal): bool
    {
        if (! $this->activo) {
            return false;
        }

        $hoy = Carbon::today();

        if ($this->fecha_inicio && $hoy->lt($this->fecha_inicio)) {
            return false;
        }

        if ($this->fecha_vencimiento && $hoy->gt($this->fecha_vencimiento)) {
            return false;
        }

        if ($this->uso_maximo !== null && $this->usos_actuales >= $this->uso_maximo) {
            return false;
        }

        if ($this->monto_minimo !== null && $subtotal < (float) $this->monto_minimo) {
            return false;
        }

        return true;
    }

    public function calcularDescuento(float $subtotal): float
    {
        if ($this->tipo === 'porcentaje') {
            return round($subtotal * ((float) $this->valor / 100), 2);
        }

        return min((float) $this->valor, $subtotal);
    }
}
