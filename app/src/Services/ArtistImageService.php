<?php
namespace App\Services;

use App\Repositories\ArtistImageRepository;
use App\Repositories\Interfaces\IArtistImageRepository;
use App\Services\Interfaces\IArtistImageService;
use Exception;

class ArtistImageService implements IArtistImageService 
{
    private $targetDir = 'C:/Users/david/Desktop/School/Year 2/Term 3/WebDev/Haarlem-Project-Festival/app/public/assets/images/';

    private IArtistImageRepository $imageRepository;
    public function __construct()
    {
        $this->imageRepository = new ArtistImageRepository();
    }

    public function uploadImage(int $artistId, array $file): string 
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error code: " . $file['error']);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception("Invalid file type.");
        }

        // 2. Generate a unique filename to prevent overwriting
        // Example: artist_5_167888223.jpg
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = "artist_" . $artistId . "_" . time() . "." . $extension;

        // 3. Define the full destination path
        $destination = $this->targetDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            
            // 5. Save ONLY the relative path or filename to DB
            // This makes it easier to use in HTML like <img src="/assets/images/...">
            $dbPath = $newFilename; 
            
            $this->imageRepository->addImage($artistId, $dbPath);
            
            return "Image uploaded successfully!";
        } else {
            throw new Exception("Failed to move uploaded file.");
        }
    }

}