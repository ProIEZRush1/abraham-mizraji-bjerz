<?php

return [
    // Access Token de la cuenta de Mercado Pago del negocio (Checkout Pro / API).
    // Sin este valor, el checkout real queda deshabilitado y la UI lo refleja
    // con un aviso, sin romper la app (degradación elegante).
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
];
