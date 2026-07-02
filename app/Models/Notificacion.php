<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notificaciones';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensaje',
        'enlace',
        'leida_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'leida_at' => 'datetime',
        ];
    }

    /**
     * Usuario dueño de la notificación.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Limita la consulta a notificaciones sin leer.
     *
     * @param  Builder<Notificacion>  $query
     */
    public function scopeNoLeidas(Builder $query): void
    {
        $query->whereNull('leida_at');
    }

    /**
     * Indica si la notificación ya fue leída.
     */
    public function estaLeida(): bool
    {
        return $this->leida_at !== null;
    }
}
