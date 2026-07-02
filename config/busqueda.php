<?php

use App\Models\User;

return [
    /*
    |--------------------------------------------------------------------------
    | Modelos buscables (BÚSQUEDA GLOBAL)
    |--------------------------------------------------------------------------
    |
    | Registro de los modelos que la búsqueda global recorre. Cada módulo por
    | cliente puede registrar aquí sus propios modelos sin tocar el servicio.
    |
    | Cada entrada admite las siguientes claves:
    |
    |   'model'     => Clase Eloquent a consultar (obligatorio).
    |   'label'     => Nombre visible del grupo de resultados.
    |   'icon'      => Un solo emoji para el grupo.
    |   'columns'   => Columnas donde se aplica el filtro LIKE.
    |   'title'     => Columna usada como título de cada resultado.
    |   'subtitle'  => Columna usada como subtítulo (opcional).
    |   'route'     => Nombre de ruta para enlazar el registro (opcional).
    |   'route_key' => Atributo que se pasa a la ruta (por defecto la PK).
    |
    | El servicio degrada con elegancia: si un modelo, su tabla o sus columnas
    | no existen, simplemente se omite ese grupo (nunca lanza un 500).
    |
    */

    // Máximo de resultados mostrados por modelo.
    'per_model' => 5,

    'models' => [
        [
            'model' => User::class,
            'label' => 'Usuarios',
            'icon' => '👤',
            'columns' => ['name', 'email'],
            'title' => 'name',
            'subtitle' => 'email',
            'route' => null,
            'route_key' => 'id',
        ],
    ],
];
