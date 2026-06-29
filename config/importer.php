<?php

use App\Models\ImporterContact;

return [
    /*
    |--------------------------------------------------------------------------
    | Destinos importables
    |--------------------------------------------------------------------------
    |
    | Cada destino define un modelo Eloquent al que se pueden importar filas
    | desde un CSV, junto con los campos que acepta. El importador genérico
    | lee esta lista para poblar el selector de destino y la pantalla de
    | mapeo de columnas.
    |
    | Estructura de un destino:
    |
    |   'clave' => [
    |       'label'  => 'Texto visible',
    |       'model'  => \App\Models\MiModelo::class,
    |       'fields' => [
    |           'columna_db' => ['label' => 'Etiqueta', 'required' => true],
    |       ],
    |   ],
    |
    | Solo se muestran los destinos cuyo modelo existe, de modo que agregar o
    | quitar destinos nunca rompe la aplicación.
    |
    */
    'targets' => [
        'contactos' => [
            'label' => 'Contactos',
            'model' => ImporterContact::class,
            'fields' => [
                'nombre' => ['label' => 'Nombre', 'required' => true],
                'email' => ['label' => 'Correo electrónico', 'required' => false],
                'telefono' => ['label' => 'Teléfono', 'required' => false],
                'empresa' => ['label' => 'Empresa', 'required' => false],
                'notas' => ['label' => 'Notas', 'required' => false],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Límites
    |--------------------------------------------------------------------------
    */
    'max_file_kb' => 10240,   // Tamaño máximo del archivo CSV (KB).
    'sample_rows' => 5,       // Filas de muestra mostradas en la previsualización.
    'chunk_size' => 500,      // Filas insertadas por lote durante la importación.
];
