<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Capacidad: Auditoría.
 *
 * Muestra la Bitácora: el listado filtrable de todas las operaciones
 * (creación / actualización / eliminación) registradas por el trait
 * LogsActivity.
 */
class AuditoriaController extends Controller
{
    /**
     * Listado filtrable de la bitácora.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'event' => $request->string('event')->toString() ?: null,
            'auditable_type' => $request->string('auditable_type')->toString() ?: null,
            'user_id' => $request->string('user_id')->toString() ?: null,
            'from' => $request->string('from')->toString() ?: null,
            'to' => $request->string('to')->toString() ?: null,
            'search' => $request->string('search')->toString() ?: null,
        ];

        $logs = AuditLog::query()
            ->with('user:id,name')
            ->event($filters['event'])
            ->auditable($filters['auditable_type'])
            ->byUser($filters['user_id'])
            ->betweenDates($filters['from'], $filters['to'])
            ->search($filters['search'])
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (AuditLog $log) => [
                'id' => $log->id,
                'event' => $log->event,
                'event_label' => $log->event_label,
                'auditable_type' => $log->auditable_type,
                'auditable_label' => $log->auditable_label,
                'auditable_id' => $log->auditable_id,
                'user_name' => $log->user_name,
                'changes' => $log->changes,
                'ip_address' => $log->ip_address,
                'url' => $log->url,
                'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                'created_at_human' => $log->created_at?->diffForHumans(),
            ]);

        return Inertia::render('Auditoria/Index', [
            'logs' => $logs,
            'filters' => $filters,
            'options' => [
                'events' => $this->eventOptions(),
                'types' => $this->typeOptions(),
                'users' => $this->userOptions(),
            ],
        ]);
    }

    /**
     * Opciones de evento disponibles para el filtro.
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function eventOptions(): array
    {
        return [
            ['value' => 'created', 'label' => 'Creación'],
            ['value' => 'updated', 'label' => 'Actualización'],
            ['value' => 'deleted', 'label' => 'Eliminación'],
        ];
    }

    /**
     * Tipos de modelo presentes en la bitácora (para el filtro).
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function typeOptions(): array
    {
        return AuditLog::query()
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => class_basename($type),
            ])
            ->values()
            ->all();
    }

    /**
     * Usuarios que han generado registros (para el filtro).
     *
     * @return array<int, array{value: int, label: string}>
     */
    protected function userOptions(): array
    {
        $ids = AuditLog::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        return User::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => [
                'value' => $user->id,
                'label' => $user->name,
            ])
            ->all();
    }
}
