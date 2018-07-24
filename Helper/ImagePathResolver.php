<?php

namespace Metrilo\Analytics\Helper;

class ImagePathResolver extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Takes the best quality image url with timestamp of last modification
     * @param $product / store product
     * @return string
     */
    public function getBaseImage($product) {

        $imagePath = $product->getMediaGalleryImages()->getFirstItem()->getPath();
        $imageUrl = $product->getMediaGalleryImages()->getFirstItem()->getUrl();
        $imageMtime = '';
        $timestampUrl = '';

        if (file_exists($imagePath)) {
            $imageMtime = strtotime(date("F d Y H:i:s.", filemtime($imagePath)));
        }  

        if(isset($imageUrl) && !empty($imageMtime)) {
            $timestampUrl = $imageUrl . '?t=' . $imageMtime;
        }

        return $timestampUrl;
    }

}