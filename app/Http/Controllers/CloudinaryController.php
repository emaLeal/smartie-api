<?php

namespace App\Http\Controllers;

use App\Traits\ApiExceptions;
use Cloudinary\Cloudinary;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class CloudinaryController extends Controller
{
   use ApiExceptions;

    public $cloudinary;

    public function __construct() {
        $this->cloudinary = new Cloudinary();
    }

    public function uploadImage(UploadedFile $file, String $folder) {
        $path = $file->getRealPath();

        $result = $this->cloudinary
                       ->uploadApi()
                       ->upload($path, ['folder' => $folder]
                       );

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id']
            ];
    }

    public function deleteImage(?string $publicId) {
        if (empty($publicId)) {
            return false;
        }

        $this->cloudinary
             ->uploadApi()
             ->destroy($publicId);

    return true;
}}

