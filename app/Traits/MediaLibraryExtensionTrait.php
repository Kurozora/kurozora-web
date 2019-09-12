<?php

namespace App\Traits;

trait MediaLibraryExtensionTrait {
    function getFirstMediaFullUrl($mediaName) {
        $media = $this->getFirstMedia($mediaName);

        if($media == null) return null;
        else return $media->getFullUrl();
    }
}
