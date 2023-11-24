<?php

namespace PERP\Utilities\File;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class  FileService
{
    public function uploadFile($file, $filePath = '',  $prefix = '', $acceptableMIME = 'mimes:pdf,docx')
    {
        if (!$file->isValid()) {
            throw new \Exception("Invalid file input", 400);
        }

        $ext = strtolower($file->getClientOriginalExtension()); // You can use also getClientOriginalName()

        $filename =  $file->getClientOriginalName();
        if ($prefix) {
            $filename =  uniqid($prefix) . '.' . $ext;
        }

        $file->storeAs($filePath, $filename);

        return $filename;
    }

    public function retrieveFile($path, $filename)
    {
        if (!$path) {
            throw new \Exception("Invalid file path", 400);
        }

        if (!Storage::exists($path . $filename)) { //
            throw new \Exception("File {$filename} does not exist", 404);
        }

        $contents = Storage::get($path . $filename);
        $mimeType = Storage::mimeType($path . $filename);

        return [
            'file' => $contents,
            'mime' => $mimeType
        ];
    }

    public function retrieveFileContents($path, $filename)
    {
        if (!$path) {
            throw new \Exception("Invalid file path", 400);
        }

        if (!Storage::exists($path . $filename)) { //
            throw new \Exception("File {$filename} does not exist", 404);
        }

        return Storage::get($path . $filename);;
    }

    public function saveFile($path, $data, $filename)
    {
        if (!$path) throw new \Exception("File destination is missing.", 404);

        return Storage::put($path . $filename, $data);
    }

    public function saveStream($path, $data, $filename)
    {
        Log::info('Saving Stream: ' . $filename);

        if (!$path) throw new \Exception("Stream destination is missing.", 404);

        return Storage::put($path . $filename, $data);
    }

    public function deleteFile($path, $filename)
    {
        if (!$path) {
            throw new \Exception("Invalid file path", 400);
        }

        if (!Storage::exists($path . $filename)) { //
            throw new \Exception("File {$filename} does not exist", 404);
        }

        return Storage::delete($path . $filename);
    }

    public function saveBase64ToFile($base64File, $filePath, $filename)
    {
        Storage::put($filePath . $filename, base64_decode($base64File));

        return $filename;
    }

    public static function convertFilesize($bytes, $decimals = 2)
    {
        $size = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " " . @$size[$factor];
    }
}
