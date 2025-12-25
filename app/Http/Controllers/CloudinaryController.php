<?php

namespace App\Http\Controllers;

use App\Traits\ApiExceptions;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

/**
 * Class CloudinaryController
 * Cloudinary manager to manage the logic,
 * uploading and deleting the file obtained
 * @package App\Http\Controller
 **/
class CloudinaryController extends Controller
{
   use ApiExceptions;

    public $cloudinary;

   /**
    * Constructor, create the cloudinary property and instance it with the Cloudinary
    * offial library
    **/
    public function __construct() {
        $this->cloudinary = new Cloudinary();
    }

    /**
     * Manages the upload of an image to cloudinary and returns the secure url and public id
     * to be managed in the database
     * @param \Illuminate\Http\UploadedFile $file the file to be uploaded
     * @param String $folder the folder where the image will be stored
     * @return array with the attributes 'url' with the img 'secure_url' and the 'public_id' with the public id
     **/
    public function uploadImage(UploadedFile $file, String $folder): array {
        // Get the tmp real path
        $path = $file->getRealPath();

        // Upload the file to the folder indicated
        $result = $this->cloudinary
                       ->uploadApi()
                       ->upload($path, ['folder' => $folder]
                       );

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id']
            ];
    }

    /**
     * Delete image previously uploaded with the public id of the file
     * @param $publicId The id every uploaded file gets and the way to erase it
     * @return bool indicating if the file was deleted or not
     **/
    public function deleteImage(?string $publicId): bool {
        // Validates the public id is not empty
        if (empty($publicId)) {
            return false;
        }

        // Deletes the file
        $this->cloudinary
             ->uploadApi()
             ->destroy($publicId);

        return true;
    }
}

