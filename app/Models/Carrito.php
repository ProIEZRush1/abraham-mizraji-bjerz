<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrito extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CarritoItem::class);
    }

    public function subtotal(): float
    {
        return (float) $this->items->sum(fn (CarritoItem $item) => $item->cantidad * (float) $item->precio_unitario);
    }

    public function cantidadTotal(): int
    {
        return (int) $this->items->sum('cantidad');
    }
}
