<?php

namespace PERP\Traits;

use PERP\Log\Services\SystemLogService;
use PERP\Utilities\Image\ImageService;

trait CreateThumbnail
{
    public function createThumbnails($filePath, $imageFileName)
    {
        try {
            $imageFile = (new ImageService)->retrieveImageData($filePath, $imageFileName);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }

        try {
            (new ImageService)->resize($imageFile, $filePath . '/thumbs//' . $imageFileName);
        } catch (\Exception $e) {
            SystemLogService::exception($e);

            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
