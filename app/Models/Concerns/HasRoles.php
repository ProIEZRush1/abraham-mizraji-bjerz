<?php

namespace App\Models\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Aporta gestión ligera de roles/permisos a un modelo (típicamente User).
 *
 * Todas las comprobaciones degradan con elegancia: si las tablas de roles aún
 * no existen (despliegue parcial / migración pendiente) devuelven false en vez
 * de lanzar una excepción, evitando errores 500.
 */
trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $name): bool
    {
        return $this->hasAnyRole([$name]);
    }

    public function hasAnyRole(array $names): bool
    {
        $names = array_filter($names);

        if (empty($names)) {
            return false;
        }

        try {
            return $this->roles()->whereIn('name', $names)->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function hasPermission(string $name): bool
    {
        try {
            return $this->roles()
                ->whereHas('permissions', fn ($query) => $query->where('name', $name))
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }
}
