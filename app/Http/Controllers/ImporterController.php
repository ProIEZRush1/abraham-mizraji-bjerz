<?php

namespace App\Http\Controllers;

use App\Models\ImporterLog;
use App\Services\ImporterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ImporterController extends Controller
{
    private const TMP_DIR = 'importer-tmp';

    public function __construct(private readonly ImporterService $importer) {}

    /**
     * Muestra la pantalla de importación con los destinos disponibles
     * y el historial reciente de importaciones.
     */
    public function index(): Response
    {
        return Inertia::render('Importer/Index', [
            'targets' => $this->targetsPayload(),
            'logs' => $this->recentLogs(),
        ]);
    }

    /**
     * Recibe el CSV, lo guarda temporalmente y devuelve una previsualización
     * (cabeceras y filas de muestra) para que el usuario mapee las columnas.
     */
    public function analyze(Request $request): Response
    {
        $maxKb = (int) config('importer.max_file_kb', 10240);

        $validated = $request->validate([
            'target' => ['required', 'string'],
            'has_header' => ['required', 'boolean'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:'.$maxKb],
        ]);

        $target = $this->importer->target($validated['target']);
        if ($target === null) {
            throw ValidationException::withMessages([
                'target' => 'El destino seleccionado no es válido.',
            ]);
        }

        $token = Str::uuid()->toString().'.csv';
        Storage::disk('local')->putFileAs(self::TMP_DIR, $request->file('file'), $token);
        $path = Storage::disk('local')->path(self::TMP_DIR.'/'.$token);

        $analysis = $this->importer->analyze(
            $path,
            (bool) $validated['has_header'],
            (int) config('importer.sample_rows', 5),
        );

        return Inertia::render('Importer/Index', [
            'targets' => $this->targetsPayload(),
            'logs' => $this->recentLogs(),
            'analysis' => [
                'token' => $token,
                'target' => $validated['target'],
                'has_header' => (bool) $validated['has_header'],
                'filename' => $request->file('file')->getClientOriginalName(),
                'headers' => $analysis['headers'],
                'sample' => $analysis['sample'],
                'total' => $analysis['total'],
            ],
        ]);
    }

    /**
     * Ejecuta la importación masiva usando el archivo temporal y el mapeo
     * de columnas enviado por el usuario.
     */
    public function import(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'target' => ['required', 'string'],
            'has_header' => ['required', 'boolean'],
            'mapping' => ['required', 'array'],
        ]);

        $target = $this->importer->target($validated['target']);
        if ($target === null) {
            throw ValidationException::withMessages([
                'target' => 'El destino seleccionado no es válido.',
            ]);
        }

        $token = basename($validated['token']);
        if (! preg_match('/^[a-f0-9\-]{36}\.csv$/i', $token)) {
            throw ValidationException::withMessages([
                'token' => 'El archivo de importación no es válido. Vuelve a subirlo.',
            ]);
        }

        $relative = self::TMP_DIR.'/'.$token;
        if (! Storage::disk('local')->exists($relative)) {
            return redirect()->route('importer.index')
                ->withErrors(['file' => 'El archivo expiró o ya no está disponible. Súbelo de nuevo.']);
        }

        // Normaliza el mapeo: solo campos del destino, índices enteros válidos.
        $mapping = [];
        foreach ($target['fields'] as $field => $meta) {
            $value = $validated['mapping'][$field] ?? null;
            if ($value !== null && $value !== '' && is_numeric($value)) {
                $mapping[$field] = (int) $value;
            }
        }

        // Verifica que los campos obligatorios estén mapeados.
        foreach ($target['fields'] as $field => $meta) {
            if (($meta['required'] ?? false) && ! isset($mapping[$field])) {
                throw ValidationException::withMessages([
                    'mapping' => "Debes asignar una columna al campo obligatorio \"{$meta['label']}\".",
                ]);
            }
        }

        $path = Storage::disk('local')->path($relative);

        $result = $this->importer->import(
            $target,
            $path,
            (bool) $validated['has_header'],
            $mapping,
            (int) config('importer.chunk_size', 500),
        );

        Storage::disk('local')->delete($relative);

        ImporterLog::create([
            'user_id' => $request->user()?->id,
            'target' => $validated['target'],
            'filename' => Str::limit($token, 255, ''),
            'total' => $result['total'],
            'imported' => $result['imported'],
            'failed' => $result['failed'],
            'errors' => $result['errors'],
        ]);

        return Inertia::render('Importer/Index', [
            'targets' => $this->targetsPayload(),
            'logs' => $this->recentLogs(),
            'result' => array_merge($result, [
                'target_label' => $target['label'],
            ]),
        ]);
    }

    /**
     * Estructura los destinos para el frontend (sin exponer clases de modelo).
     *
     * @return array<int, array<string, mixed>>
     */
    private function targetsPayload(): array
    {
        $payload = [];
        foreach ($this->importer->targets() as $key => $target) {
            $fields = [];
            foreach ($target['fields'] as $field => $meta) {
                $fields[] = [
                    'name' => $field,
                    'label' => $meta['label'] ?? $field,
                    'required' => (bool) ($meta['required'] ?? false),
                ];
            }

            $payload[] = [
                'key' => $key,
                'label' => $target['label'] ?? $key,
                'fields' => $fields,
            ];
        }

        return $payload;
    }

    /**
     * Historial reciente de importaciones.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentLogs(): array
    {
        return ImporterLog::query()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (ImporterLog $log) => [
                'id' => $log->id,
                'target' => $log->target,
                'total' => $log->total,
                'imported' => $log->imported,
                'failed' => $log->failed,
                'created_at' => $log->created_at?->format('d/m/Y H:i'),
            ])
            ->all();
    }
}
