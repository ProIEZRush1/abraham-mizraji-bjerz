<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImporterLog extends Model
{
    protected $table = 'importer_logs';

    protected $fillable = [
        'user_id',
        'target',
        'filename',
        'total',
        'imported',
        'failed',
        'errors',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'total' => 'integer',
            'imported' => 'integer',
            'failed' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
