<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'name', 'disk', 'path', 'mime_type', 'extension', 'size'])]
class MediaFile extends Model
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['url', 'is_image', 'size_human'];

    /**
     * Public URL for the stored file.
     */
    public function getUrlAttribute(): string
    {
        try {
            return Storage::disk($this->disk ?: 'public')->url($this->path);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Whether the file is an image (for thumbnail rendering).
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    /**
     * Human readable file size.
     */
    public function getSizeHumanAttribute(): string
    {
        $bytes = (int) $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return ($i === 0 ? $bytes : number_format($bytes, 1)).' '.$units[$i];
    }
}
