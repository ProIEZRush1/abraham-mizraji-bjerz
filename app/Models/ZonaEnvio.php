<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonaEnvio extends Model
{
    protected $table = 'zonas_envio';

    protected $fillable = ['nombre', 'provincias', 'costo', 'tiempo_estimado', 'activo'];

    protected function casts(): array
    {
        return [
            'provincias' => 'array',
            'costo' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public static function paraProvincia(string $provincia): ?self
    {
        return static::where('activo', true)
            ->get()
            ->first(fn (self $zona) => in_array($provincia, $zona->provincias ?? [], true));
    }
}
