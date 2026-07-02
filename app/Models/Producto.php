<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria_id', 'nombre', 'slug', 'descripcion', 'precio', 'precio_oferta',
        'sku', 'stock', 'stock_minimo', 'imagen_principal', 'imagenes', 'activo', 'destacado',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'precio_oferta' => 'decimal:2',
            'imagenes' => 'array',
            'activo' => 'boolean',
            'destacado' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function variantes(): HasMany
    {
        return $this->hasMany(ProductoVariante::class);
    }

    public function precioFinal(): float
    {
        return (float) ($this->precio_oferta ?: $this->precio);
    }

    public function stockDisponible(): int
    {
        if ($this->variantes()->exists()) {
            return (int) $this->variantes()->where('activo', true)->sum('stock');
        }

        return (int) $this->stock;
    }

    public function bajoStock(): bool
    {
        return $this->stockDisponible() <= $this->stock_minimo;
    }
}
