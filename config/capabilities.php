<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navegación de capacidades
    |--------------------------------------------------------------------------
    |
    | Se comparte con Inertia mediante HandleInertiaRequests (prop
    | 'capabilities') y AuthenticatedLayout la renderiza en la barra lateral
    | del panel de administración. Cada entrada: ['label', 'href', 'icon'].
    |
    */
    'nav' => [
        ['label' => 'Productos', 'href' => '/productos', 'icon' => '🛍️'],
        ['label' => 'Categorías', 'href' => '/categorias', 'icon' => '🗂️'],
        ['label' => 'Pedidos', 'href' => '/pedidos', 'icon' => '📦'],
        ['label' => 'Cupones', 'href' => '/cupones', 'icon' => '🏷️'],
        ['label' => 'Envíos', 'href' => '/envios', 'icon' => '🚚'],
        ['label' => 'Reportes', 'href' => '/reportes', 'icon' => '📊'],
        ['label' => 'Usuarios y roles', 'href' => '/usuarios', 'icon' => '👥'],
        ['label' => 'Archivos', 'href' => '/archivos', 'icon' => '🖼️'],
        ['label' => 'Notificaciones', 'href' => '/notificaciones', 'icon' => '🔔'],
        ['label' => 'Búsqueda', 'href' => '/buscar', 'icon' => '🔍'],
        ['label' => 'Importar CSV', 'href' => '/importar', 'icon' => '📥'],
        ['label' => 'API y tokens', 'href' => '/api', 'icon' => '🔌'],
        ['label' => 'Auditoría', 'href' => '/auditoria', 'icon' => '📝'],
        ['label' => 'Ajustes', 'href' => '/ajustes', 'icon' => '⚙️'],
    ],
];
