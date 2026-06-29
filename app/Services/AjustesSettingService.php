<?php

namespace App\Services;

use App\Models\AjusteSetting;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Lectura/escritura de los ajustes y la marca del negocio (tabla clave/valor).
 *
 * Toda operación degrada con elegancia: si la tabla aún no existe (despliegue
 * sin migrar) se devuelven los valores por defecto y nunca se lanza un 500.
 */
class AjustesSettingService
{
    private const CACHE_KEY = 'ajustes.settings';

    /**
     * Valores por defecto de cada ajuste. También definen las claves válidas.
     *
     * @return array<string, string|null>
     */
    public function defaults(): array
    {
        return [
            'business_name' => config('app.name'),
            'logo_url' => null,
            'brand_color_primary' => '#7c3aed',
            'brand_color_secondary' => '#c026d3',
            'contact_email' => null,
            'contact_phone' => null,
            'contact_address' => null,
        ];
    }

    /**
     * Todos los ajustes (valores por defecto fusionados con los guardados).
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        $stored = $this->stored();

        return array_merge($this->defaults(), $stored);
    }

    /**
     * Obtiene un único ajuste.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Guarda varios ajustes a la vez. Las claves desconocidas se ignoran.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values): void
    {
        $allowed = array_keys($this->defaults());

        foreach ($values as $key => $value) {
            if (! in_array($key, $allowed, true)) {
                continue;
            }

            AjusteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value === '' ? null : $value],
            );
        }

        $this->forget();
    }

    /**
     * Carga útil de marca compartida con Inertia en cada respuesta.
     *
     * @return array<string, mixed>
     */
    public function branding(): array
    {
        $settings = $this->all();

        return [
            'business_name' => $settings['business_name'] ?: config('app.name'),
            'logo_url' => $settings['logo_url'],
            'colors' => [
                'primary' => $settings['brand_color_primary'],
                'secondary' => $settings['brand_color_secondary'],
            ],
            'contact' => [
                'email' => $settings['contact_email'],
                'phone' => $settings['contact_phone'],
                'address' => $settings['contact_address'],
            ],
        ];
    }

    /**
     * Ajustes persistidos, cacheados y tolerantes a fallos.
     *
     * @return array<string, string|null>
     */
    private function stored(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                return AjusteSetting::query()
                    ->pluck('value', 'key')
                    ->toArray();
            });
        } catch (Throwable $e) {
            return [];
        }
    }

    private function forget(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable $e) {
            // El olvido de caché no debe interrumpir el guardado.
        }
    }
}
