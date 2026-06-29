<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Servicio de BÚSQUEDA GLOBAL.
 *
 * Recorre los modelos declarados en config('busqueda.models') aplicando un
 * filtro LIKE sobre las columnas configuradas y agrupa los resultados por
 * modelo. Es totalmente config-driven: los módulos por cliente solo agregan
 * entradas al archivo de configuración, sin modificar este servicio.
 */
class BusquedaService
{
    /**
     * Ejecuta la búsqueda global sobre todos los modelos registrados.
     *
     * @return array<int, array<string, mixed>> Grupos de resultados.
     */
    public function search(string $term, ?int $perModel = null): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $perModel = $perModel ?? (int) config('busqueda.per_model', 5);
        $sources = config('busqueda.models', []);

        $groups = [];

        foreach ($sources as $source) {
            $group = $this->searchModel((array) $source, $term, $perModel);

            if ($group !== null && $group['count'] > 0) {
                $groups[] = $group;
            }
        }

        return $groups;
    }

    /**
     * Busca dentro de un único modelo declarado en la configuración.
     *
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>|null
     */
    protected function searchModel(array $source, string $term, int $perModel): ?array
    {
        $modelClass = $source['model'] ?? null;
        $columns = $source['columns'] ?? [];

        if (! is_string($modelClass) || ! class_exists($modelClass) || empty($columns)) {
            return null;
        }

        try {
            $instance = new $modelClass;

            if (! $instance instanceof Model) {
                return null;
            }

            $table = $instance->getTable();

            if (! Schema::hasTable($table)) {
                return null;
            }

            // Conserva solo las columnas que realmente existen en la tabla.
            $columns = array_values(array_filter(
                $columns,
                fn ($column) => is_string($column) && Schema::hasColumn($table, $column)
            ));

            if (empty($columns)) {
                return null;
            }

            $like = '%'.$term.'%';

            $query = $modelClass::query()->where(function ($builder) use ($columns, $like) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', $like);
                }
            });

            $count = (clone $query)->count();
            $records = $query->limit($perModel)->get();

            $items = $records
                ->map(fn (Model $record) => $this->formatRecord($record, $source))
                ->all();

            return [
                'key' => $source['key'] ?? Str::slug($source['label'] ?? class_basename($modelClass)),
                'label' => $source['label'] ?? class_basename($modelClass),
                'icon' => $source['icon'] ?? '📄',
                'count' => $count,
                'items' => $items,
            ];
        } catch (\Throwable $e) {
            // Degradación elegante: un modelo problemático no rompe la búsqueda.
            report($e);

            return null;
        }
    }

    /**
     * Da formato a un registro para el frontend.
     *
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    protected function formatRecord(Model $record, array $source): array
    {
        $titleColumn = $source['title'] ?? null;
        $subtitleColumn = $source['subtitle'] ?? null;

        $title = $titleColumn ? ($record->{$titleColumn} ?? null) : null;

        if ($title === null || $title === '') {
            $title = '#'.$record->getKey();
        }

        $subtitle = '';
        if ($subtitleColumn) {
            $subtitle = (string) ($record->{$subtitleColumn} ?? '');
        }

        return [
            'id' => $record->getKey(),
            'title' => (string) $title,
            'subtitle' => $subtitle,
            'url' => $this->resolveUrl($record, $source),
        ];
    }

    /**
     * Resuelve la URL del registro si la configuración define una ruta válida.
     *
     * @param  array<string, mixed>  $source
     */
    protected function resolveUrl(Model $record, array $source): ?string
    {
        $routeName = $source['route'] ?? null;

        if (! is_string($routeName) || $routeName === '' || ! Route::has($routeName)) {
            return null;
        }

        try {
            $routeKey = $source['route_key'] ?? $record->getKeyName();
            $value = $record->{$routeKey} ?? $record->getKey();

            return route($routeName, [$value]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
