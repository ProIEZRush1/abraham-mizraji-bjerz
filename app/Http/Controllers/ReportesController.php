<?php

namespace App\Http\Controllers;

use App\Services\ReportesService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesController extends Controller
{
    public function __construct(private readonly ReportesService $reportes) {}

    /**
     * Muestra el panel de reportes con tarjetas de resumen y gráfica.
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('Reportes/Index', [
            'resumen' => $this->reportes->resumen(),
            'serie' => $this->reportes->serieDiaria(),
            'categorias' => $this->reportes->porCategoria(),
            'productosMasVendidos' => $this->reportes->productosMasVendidos(),
            'generadoEl' => CarbonImmutable::now()->translatedFormat('d M Y, H:i'),
        ]);
    }

    /**
     * Exporta todas las métricas a un archivo CSV descargable.
     */
    public function exportCsv(): StreamedResponse
    {
        $registros = $this->reportes->registros();
        $nombre = 'reportes-'.CarbonImmutable::now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($registros) {
            $salida = fopen('php://output', 'w');

            // BOM para que Excel reconozca UTF-8 (acentos en español).
            fwrite($salida, "\xEF\xBB\xBF");

            fputcsv($salida, ['ID', 'Categoría', 'Etiqueta', 'Valor', 'Fecha']);

            foreach ($registros as $registro) {
                fputcsv($salida, [
                    $registro->id,
                    $registro->categoria,
                    $registro->etiqueta,
                    number_format((float) $registro->valor, 2, '.', ''),
                    optional($registro->ocurrido_el)->format('Y-m-d'),
                ]);
            }

            fclose($salida);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Exporta el reporte a PDF.
     *
     * Si el paquete barryvdh/laravel-dompdf está instalado se genera un PDF
     * real; de lo contrario se entrega una versión HTML imprimible para que la
     * función nunca falle (degradación elegante).
     */
    public function exportPdf(): Response
    {
        $datos = [
            'resumen' => $this->reportes->resumen(),
            'categorias' => $this->reportes->porCategoria(),
            'productosMasVendidos' => $this->reportes->productosMasVendidos(),
            'registros' => $this->reportes->registros(),
            'generadoEl' => CarbonImmutable::now()->translatedFormat('d M Y, H:i'),
        ];

        $facade = '\Barryvdh\DomPDF\Facade\Pdf';

        if (class_exists($facade)) {
            $pdf = $facade::loadView('reportes.pdf', $datos);

            return $pdf->download('reportes-'.CarbonImmutable::now()->format('Y-m-d-His').'.pdf');
        }

        // Respaldo: HTML imprimible (el navegador puede guardarlo como PDF).
        return response()
            ->view('reportes.pdf', $datos)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
