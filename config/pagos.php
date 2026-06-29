<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de la capacidad Pagos (Stripe)
    |--------------------------------------------------------------------------
    |
    | Todas las llaves se leen del entorno. Si STRIPE_SECRET no está definido,
    | la capacidad degrada de forma elegante: la interfaz sigue funcionando,
    | se pueden registrar cobros, pero no se generan enlaces de pago en línea.
    |
    */

    'secret' => env('STRIPE_SECRET'),

    'public' => env('STRIPE_KEY'),

    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    // Moneda ISO de 3 letras en minúsculas (mxn, usd, eur, ...).
    'currency' => env('PAGOS_CURRENCY', 'mxn'),
];
