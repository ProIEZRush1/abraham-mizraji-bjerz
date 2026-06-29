<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de bitácora / auditoría.
 *
 * Capacidad: Auditoría. Cada fila representa una operación
 * (created/updated/deleted) sobre un modelo que use el trait LogsActivity.
 */
class AuditLog extends Model
{
    /** Esta tabla solo se inserta; nunca se actualiza tras crearse. */
    protected $fillable = [
        'user_id',
        'user_name',
        'event',
        'auditable_type',
        'auditable_id',
        'changes',
        'ip_address',
        'url',
    ];

    /**
     * Atributos casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    /**
     * Usuario que realizó la acción (puede ser nulo si fue el sistema).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Etiqueta legible (en español) del evento registrado.
     */
    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created' => 'Creación',
            'updated' => 'Actualización',
            'deleted' => 'Eliminación',
            default => ucfirst((string) $this->event),
        };
    }

    /**
     * Nombre corto del modelo afectado (sin namespace).
     */
    public function getAuditableLabelAttribute(): string
    {
        return class_basename((string) $this->auditable_type);
    }

    /**
     * Filtra por evento si se proporciona.
     */
    public function scopeEvent(Builder $query, ?string $event): Builder
    {
        return $query->when($event, fn (Builder $q) => $q->where('event', $event));
    }

    /**
     * Filtra por tipo de modelo afectado si se proporciona.
     */
    public function scopeAuditable(Builder $query, ?string $type): Builder
    {
        return $query->when($type, fn (Builder $q) => $q->where('auditable_type', $type));
    }

    /**
     * Filtra por usuario si se proporciona.
     */
    public function scopeByUser(Builder $query, int|string|null $userId): Builder
    {
        return $query->when($userId, fn (Builder $q) => $q->where('user_id', $userId));
    }

    /**
     * Filtra por rango de fechas (inclusive) si se proporcionan.
     */
    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('created_at', '<=', $to));
    }

    /**
     * Búsqueda libre sobre nombre de usuario y modelo afectado.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $q) use ($term) {
            $like = '%'.$term.'%';
            $q->where(function (Builder $inner) use ($like) {
                $inner->where('user_name', 'like', $like)
                    ->orWhere('auditable_type', 'like', $like)
                    ->orWhere('auditable_id', 'like', $like);
            });
        });
    }
}
