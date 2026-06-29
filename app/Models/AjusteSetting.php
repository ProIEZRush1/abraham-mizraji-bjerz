<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AjusteSetting extends Model
{
    protected $table = 'ajustes_settings';

    protected $fillable = [
        'key',
        'value',
    ];
}
