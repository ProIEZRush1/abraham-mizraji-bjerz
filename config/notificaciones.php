<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Envío de correo para las notificaciones
    |--------------------------------------------------------------------------
    |
    | Cuando está activo, además de la notificación dentro de la app se envía
    | un correo al usuario. Se degrada con elegancia: si el correo no está
    | configurado o falla el envío, la notificación dentro de la app se crea
    | igual y el error solo se registra en el log (nunca lanza un 500).
    |
    */
    'email_enabled' => filter_var(env('NOTIFICACIONES_EMAIL', false), FILTER_VALIDATE_BOOLEAN),
];
