<?php

if (! function_exists('trial_locked')) {
    /**
     * Único punto de verdad: ¿el sistema está en modo prueba (candado activo)?
     * Cambia con TRIAL_LOCKED=false en el entorno, sin tocar código.
     */
    function trial_locked(): bool
    {
        return (bool) config('trial.locked');
    }
}
