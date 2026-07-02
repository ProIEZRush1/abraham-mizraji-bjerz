<?php

namespace App\Services;

use App\Mail\NotificacionMail;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificacionesService
{
    /**
     * Crea una notificación dentro de la app para un usuario y, de forma
     * opcional, le envía un correo.
     *
     * @param  string  $tipo  info | exito | advertencia | error
     * @param  bool|null  $email  Forzar el envío de correo. Si es null, se usa
     *                            el valor de config('notificaciones.email_enabled').
     */
    public function notificar(
        User $usuario,
        string $titulo,
        string $mensaje,
        ?string $enlace = null,
        string $tipo = 'info',
        ?bool $email = null,
    ): Notificacion {
        $notificacion = Notificacion::create([
            'user_id' => $usuario->getKey(),
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'enlace' => $enlace,
        ]);

        $debeEnviarEmail = $email ?? (bool) config('notificaciones.email_enabled', false);

        if ($debeEnviarEmail && is_string($usuario->email) && filter_var($usuario->email, FILTER_VALIDATE_EMAIL)) {
            $this->enviarEmail($usuario, $notificacion);
        }

        return $notificacion;
    }

    /**
     * Notifica a varios usuarios con el mismo mensaje.
     *
     * @param  iterable<User>  $usuarios
     * @return list<Notificacion>
     */
    public function notificarUsuarios(
        iterable $usuarios,
        string $titulo,
        string $mensaje,
        ?string $enlace = null,
        string $tipo = 'info',
        ?bool $email = null,
    ): array {
        $creadas = [];

        foreach ($usuarios as $usuario) {
            $creadas[] = $this->notificar($usuario, $titulo, $mensaje, $enlace, $tipo, $email);
        }

        return $creadas;
    }

    /**
     * Envía el correo de la notificación, degradándose con elegancia si el
     * servicio de correo no está disponible o falla.
     */
    protected function enviarEmail(User $usuario, Notificacion $notificacion): void
    {
        try {
            Mail::to($usuario->email)->send(new NotificacionMail($notificacion));
        } catch (\Throwable $e) {
            Log::warning('notificaciones.email_failed', [
                'notificacion_id' => $notificacion->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
