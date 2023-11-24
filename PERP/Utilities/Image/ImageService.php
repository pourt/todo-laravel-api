<?php

namespace PERP\Utilities\Image;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;

class  ImageService
{
    public function uploadImage($filePath = '', $request = 'file', $useOriginalFilename = false)
    {
        Validator::make(
            request()->all(),
            [
                'file' => 'required|mimes:jpeg,png,jpg,svg|max:5000',
            ]
        );

        if (!request()->hasFile($request)) {
            throw new \Exception("No file has been uploaded", 400);
        }

        $file = request()->file($request);

        if (!$file->isValid()) {
            throw new \Exception("Invalid file input", 400);
        }

        $ext = strtolower($file->getClientOriginalExtension()); // You can use also getClientOriginalName()

        if ($useOriginalFilename) {
            $filename = strtolower(str_replace(" ", "_", pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))) . '.' . $ext;
        } else {
            $filename = uniqid('image_') . '.' . $ext;
        }

        $file->storeAs($filePath, $filename);

        return $filename;
    }

    public function uploadImageFile($filePath = '', $file = null, $useOriginalFilename = false)
    {
        $ext = strtolower($file->getClientOriginalExtension()); // You can use also getClientOriginalName()

        if ($useOriginalFilename) {
            $filename = strtolower(str_replace(" ", "_", pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))) . '.' . $ext;
        } else {
            $filename = uniqid('image_') . '.' . $ext;
        }

        $file->storeAs($filePath, $filename);

        return $filename;
    }

    public function resize($filePath, $newPath, $width = '250', $height = null)
    {
        $image_resize = Image::make($filePath);
        $image_resize->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->encode();

        return Storage::put($newPath, $image_resize->getEncoded());
    }

    public function retrieveImage($path, $filename, $thumbnail = false, $size = '')
    {
        if (!$path) {
            throw new \Exception("Invalid file path", 400);
        }

        $filePath = $path;

        if ($thumbnail) {
            $filePath = $path . 'thumbs/';
            if ($size != '') {
                $filePath = $path . 'thumbs/' . $size . '/';
            }
        }

        if (!Storage::exists($filePath . $filename)) { //
            throw new \Exception("Image file does not exist", 404);
        }

        $contents = Storage::get($filePath . $filename);
        $mimeType = Storage::mimeType($filePath . $filename);

        return [
            'file' => $contents,
            'mime' => $mimeType
        ];
    }

    public function retrieveImageData($path, $filename, $thumbnail = false, $size = '')
    {
        if (!$path) {
            throw new \Exception("Invalid file path", 400);
        }

        $filePath = $path;

        if ($thumbnail) {
            $filePath = $path . 'thumbs/';
            if ($size != '250') {
                $filePath = $path . 'thumbs/' . $size . '/';
            }
        }

        if (!Storage::exists($filePath . $filename)) { //
            throw new \Exception("Image file does not exist", 404);
        }

        $contents = Storage::get($filePath . $filename);

        return $contents;
    }

    public function saveBase64ToFile($image64, $filePath = '')
    {
        $extension = explode('/', explode(':', substr($image64, 0, strpos($image64, ';')))[1])[1];   // .jpg .png .pdf

        $replace = substr($image64, 0, strpos($image64, ',') + 1);

        // find substring fro replace here eg: data:image/png;base64,

        $image = str_replace($replace, '', $image64);

        $image = str_replace(' ', '+', $image);

        $filename = $filename = uniqid('image_') . '.' . $extension;

        Storage::put($filePath . $filename, base64_decode($image));

        return $filename;
    }
}
