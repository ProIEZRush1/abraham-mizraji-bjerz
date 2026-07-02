<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoVariante extends Model
{
    protected $fillable = ['producto_id', 'nombre', 'sku', 'precio_extra', 'stock', 'activo'];

    protected function casts(): array
    {
        return [
            'precio_extra' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
