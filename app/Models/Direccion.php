<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Direccion extends Model
{
    protected $table = 'direcciones';

    protected $fillable = [
        'user_id', 'etiqueta', 'destinatario', 'calle', 'numero', 'piso_depto',
        'ciudad', 'provincia', 'codigo_postal', 'telefono', 'predeterminada',
    ];

    protected function casts(): array
    {
        return ['predeterminada' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
