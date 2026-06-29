<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImporterContact extends Model
{
    protected $table = 'importer_contacts';

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'empresa',
        'notas',
    ];
}
