<?php

namespace App\Services;

use App\Models\MediaFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    /**
     * Disk used to store media files.
     */
    public const DISK = 'public';

    /**
     * Folder (relative to the disk root) where files are stored.
     */
    public const FOLDER = 'media';

    /**
     * Store an uploaded file on the public disk and persist its record.
     */
    public function store(UploadedFile $file, ?int $userId = null): MediaFile
    {
        $path = $file->store(self::FOLDER, self::DISK);

        return MediaFile::create([
            'user_id' => $userId,
            'name' => $file->getClientOriginalName() ?: basename($path),
            'disk' => self::DISK,
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'extension' => strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: ''),
            'size' => $file->getSize() ?: 0,
        ]);
    }

    /**
     * Delete a media file from storage and remove its record.
     */
    public function delete(MediaFile $mediaFile): void
    {
        try {
            $disk = Storage::disk($mediaFile->disk ?: self::DISK);
            if ($disk->exists($mediaFile->path)) {
                $disk->delete($mediaFile->path);
            }
        } catch (\Throwable $e) {
            // Storage may be unavailable; remove the record regardless so the
            // library never gets stuck with an undeletable entry.
        }

        $mediaFile->delete();
    }
}
