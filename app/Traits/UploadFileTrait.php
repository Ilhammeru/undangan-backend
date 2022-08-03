<?php

namespace App\Traits;

trait UploadFileTrait {
    public function upload($file, $name, $folder) {
        $store = $file->storeAs($folder, $name, 'public');
        if ($store == '') {
            return false;
        } else {
            $path = env('URL_ASSET') . '/' . $folder . '/' . $name;
            return $path;
        }
    }
}