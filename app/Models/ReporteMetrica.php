<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['categoria', 'etiqueta', 'valor', 'ocurrido_el'])]
class ReporteMetrica extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'reportes_metricas';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'ocurrido_el' => 'date',
        ];
    }
}
