<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Importador genérico de CSV.
 *
 * Lee la configuración de destinos importables (config/importer.php), parsea
 * archivos CSV con funciones nativas de PHP (fgetcsv) y realiza inserciones
 * masivas en el modelo elegido aplicando un mapeo columna → campo.
 */
class ImporterService
{
    /**
     * Devuelve los destinos importables cuyos modelos existen realmente.
     *
     * @return array<string, array<string, mixed>>
     */
    public function targets(): array
    {
        $targets = config('importer.targets', []);
        if (! is_array($targets)) {
            return [];
        }

        return array_filter($targets, function ($target) {
            return is_array($target)
                && isset($target['model'])
                && class_exists($target['model'])
                && is_subclass_of($target['model'], Model::class)
                && ! empty($target['fields']);
        });
    }

    /**
     * Devuelve un destino por su clave, o null si no existe o no es válido.
     *
     * @return array<string, mixed>|null
     */
    public function target(string $key): ?array
    {
        $targets = $this->targets();

        return $targets[$key] ?? null;
    }

    /**
     * Parsea un CSV y devuelve cabeceras, filas de muestra y total de filas.
     *
     * @return array{headers: array<int, string>, sample: array<int, array<int, string>>, total: int}
     */
    public function analyze(string $path, bool $hasHeader, int $sampleRows = 5): array
    {
        $headers = [];
        $sample = [];
        $total = 0;

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ['headers' => [], 'sample' => [], 'total' => 0];
        }

        try {
            $first = true;
            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                // Ignora líneas completamente vacías.
                if ($row === [null] || (count($row) === 1 && trim((string) $row[0]) === '')) {
                    continue;
                }

                if ($first) {
                    $first = false;
                    $row = $this->stripBom($row);

                    if ($hasHeader) {
                        $headers = array_map(fn ($v) => trim((string) $v), $row);

                        continue;
                    }

                    // Sin cabecera: genera nombres de columna genéricos.
                    $headers = array_map(
                        fn ($i) => 'Columna '.($i + 1),
                        array_keys($row)
                    );
                }

                $total++;
                if (count($sample) < $sampleRows) {
                    $sample[] = array_map(fn ($v) => (string) $v, $row);
                }
            }
        } finally {
            fclose($handle);
        }

        return [
            'headers' => $headers,
            'sample' => $sample,
            'total' => $total,
        ];
    }

    /**
     * Importa un CSV completo hacia el modelo del destino.
     *
     * @param  array<string, mixed>  $target  Definición del destino.
     * @param  array<string, int>  $mapping  Mapa campo_db => índice de columna CSV.
     * @return array{total: int, imported: int, failed: int, errors: array<int, string>}
     */
    public function import(array $target, string $path, bool $hasHeader, array $mapping, int $chunkSize = 500): array
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $target['model'];
        $fields = $target['fields'];
        $usesTimestamps = (new $modelClass)->usesTimestamps();

        $imported = 0;
        $failed = 0;
        $total = 0;
        $errors = [];
        $batch = [];

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ['total' => 0, 'imported' => 0, 'failed' => 0, 'errors' => ['No se pudo abrir el archivo.']];
        }

        try {
            $first = true;
            $line = 0;

            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $line++;

                if ($row === [null] || (count($row) === 1 && trim((string) $row[0]) === '')) {
                    continue;
                }

                if ($first) {
                    $first = false;
                    $row = $this->stripBom($row);
                    if ($hasHeader) {
                        continue;
                    }
                }

                $total++;
                $attributes = [];
                $rowValid = true;

                foreach ($fields as $field => $meta) {
                    $index = $mapping[$field] ?? null;
                    $value = ($index !== null && array_key_exists($index, $row))
                        ? trim((string) $row[$index])
                        : null;

                    $required = (bool) ($meta['required'] ?? false);
                    if ($required && ($value === null || $value === '')) {
                        $rowValid = false;
                        if (count($errors) < 50) {
                            $label = $meta['label'] ?? $field;
                            $errors[] = "Fila {$line}: falta el campo obligatorio \"{$label}\".";
                        }
                        break;
                    }

                    $attributes[$field] = ($value === '') ? null : $value;
                }

                if (! $rowValid) {
                    $failed++;

                    continue;
                }

                if ($usesTimestamps) {
                    $now = Carbon::now();
                    $attributes['created_at'] = $now;
                    $attributes['updated_at'] = $now;
                }

                $batch[] = $attributes;

                if (count($batch) >= $chunkSize) {
                    [$ok, $ko, $err] = $this->flush($modelClass, $batch);
                    $imported += $ok;
                    $failed += $ko;
                    if ($err !== null && count($errors) < 50) {
                        $errors[] = $err;
                    }
                    $batch = [];
                }
            }

            if (! empty($batch)) {
                [$ok, $ko, $err] = $this->flush($modelClass, $batch);
                $imported += $ok;
                $failed += $ko;
                if ($err !== null && count($errors) < 50) {
                    $errors[] = $err;
                }
            }
        } finally {
            fclose($handle);
        }

        return [
            'total' => $total,
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Inserta un lote de filas, degradando a inserción fila por fila si el
     * lote completo falla, para aislar las filas problemáticas.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<int, array<string, mixed>>  $batch
     * @return array{0: int, 1: int, 2: ?string}
     */
    private function flush(string $modelClass, array $batch): array
    {
        try {
            $modelClass::query()->insert($batch);

            return [count($batch), 0, null];
        } catch (Throwable $e) {
            // El lote falló: reintenta fila por fila para salvar las válidas.
            $ok = 0;
            $ko = 0;
            $error = null;
            foreach ($batch as $attributes) {
                try {
                    $modelClass::query()->insert([$attributes]);
                    $ok++;
                } catch (Throwable $rowError) {
                    $ko++;
                    $error ??= 'Algunas filas no se pudieron guardar: '.$rowError->getMessage();
                }
            }

            return [$ok, $ko, $error];
        }
    }

    /**
     * Elimina el BOM UTF-8 del primer valor de la primera fila si está presente.
     *
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    private function stripBom(array $row): array
    {
        if (isset($row[0]) && is_string($row[0])) {
            $row[0] = preg_replace('/^\x{FEFF}/u', '', $row[0]) ?? $row[0];
        }

        return $row;
    }
}
