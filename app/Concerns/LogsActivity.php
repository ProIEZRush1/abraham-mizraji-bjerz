<?php

namespace App\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Capacidad: Auditoría.
 *
 * Trait que convierte cualquier modelo Eloquent en auditable: registra
 * automáticamente las operaciones create / update / delete en la tabla
 * `audit_logs`, junto con el usuario responsable y los cambios aplicados.
 *
 * Uso:
 *
 *   use App\Concerns\LogsActivity;
 *
 *   class Cliente extends Model
 *   {
 *       use LogsActivity;
 *
 *       // Opcional: atributos que NO deben quedar registrados.
 *       protected array $auditExclude = ['password', 'token'];
 *   }
 *
 * La escritura de la bitácora está protegida: si la tabla aún no existe
 * (p. ej. durante las migraciones) o falla por cualquier motivo, la
 * operación principal del modelo nunca se interrumpe.
 */
trait LogsActivity
{
    /**
     * Engancha los observadores de Eloquent al arrancar el modelo.
     */
    public static function bootLogsActivity(): void
    {
        static::created(static function (Model $model): void {
            $model->recordAuditActivity('created', $model->auditableNewValues());
        });

        static::updated(static function (Model $model): void {
            $changes = $model->auditableUpdatedValues();

            if (! empty($changes['before']) || ! empty($changes['after'])) {
                $model->recordAuditActivity('updated', $changes);
            }
        });

        static::deleted(static function (Model $model): void {
            $model->recordAuditActivity('deleted', ['before' => $model->auditableSnapshot()]);
        });
    }

    /**
     * Atributos excluidos de la auditoría para este modelo.
     *
     * @return array<int, string>
     */
    protected function auditableExcluded(): array
    {
        $defaults = ['password', 'remember_token', 'created_at', 'updated_at'];
        $custom = property_exists($this, 'auditExclude') && is_array($this->auditExclude)
            ? $this->auditExclude
            : [];

        return array_values(array_unique([...$defaults, ...$this->getHidden(), ...$custom]));
    }

    /**
     * Snapshot de los atributos actuales (sin los excluidos).
     *
     * @return array<string, mixed>
     */
    protected function auditableSnapshot(): array
    {
        return collect($this->attributesToArray())
            ->except($this->auditableExcluded())
            ->all();
    }

    /**
     * Valores tras una creación.
     *
     * @return array<string, mixed>
     */
    protected function auditableNewValues(): array
    {
        return ['after' => $this->auditableSnapshot()];
    }

    /**
     * Valores antes/después tras una actualización.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function auditableUpdatedValues(): array
    {
        $excluded = $this->auditableExcluded();
        $changed = collect($this->getChanges())->except($excluded);

        $after = $changed->all();
        $before = collect($this->getOriginal())
            ->only(array_keys($after))
            ->all();

        return ['before' => $before, 'after' => $after];
    }

    /**
     * Inserta la entrada de bitácora de forma segura.
     *
     * @param  array<string, mixed>  $changes
     */
    protected function recordAuditActivity(string $event, array $changes): void
    {
        try {
            if (! Schema::hasTable('audit_logs')) {
                return;
            }

            $user = Auth::user();

            AuditLog::create([
                'user_id' => $user?->getAuthIdentifier(),
                'user_name' => $user?->name ?? 'Sistema',
                'event' => $event,
                'auditable_type' => $this->getMorphClass(),
                'auditable_id' => $this->getKey(),
                'changes' => $changes,
                'ip_address' => $this->auditRequestValue('ip'),
                'url' => $this->auditRequestValue('url'),
            ]);
        } catch (Throwable) {
            // La auditoría nunca debe romper la operación principal.
        }
    }

    /**
     * Obtiene un dato de la petición actual de forma segura (puede no existir
     * en contexto de consola, colas o seeders).
     */
    protected function auditRequestValue(string $key): ?string
    {
        try {
            $request = request();

            return match ($key) {
                'ip' => $request?->ip(),
                'url' => $request ? mb_substr((string) $request->fullUrl(), 0, 255) : null,
                default => null,
            };
        } catch (Throwable) {
            return null;
        }
    }
}
