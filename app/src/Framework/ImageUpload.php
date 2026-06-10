<?php
namespace App\Framework;

/**
 * Reusable image-upload helper for admin forms. Validates the real MIME type
 * and size, stores the file under public/assets/uploads/{subdir} with a random
 * name, and returns the public path.
 */
class ImageUpload
{
    private const ALLOWED = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_BYTES = 4 * 1024 * 1024; // 4 MB

    /**
     * Handle an optional uploaded file from $_FILES[$field].
     *
     * @return array{ok:bool,path?:string,message?:string}
     *   ok=true with no path  -> no file was submitted (caller keeps existing value)
     *   ok=true with path     -> stored successfully
     *   ok=false with message -> validation/storage error
     */
    public static function handle(string $field, string $subdir): array
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true];
        }

        $file = $_FILES[$field];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Image upload failed; please try again.'];
        }
        if ($file['size'] > self::MAX_BYTES) {
            return ['ok' => false, 'message' => 'Image is too large (max 4 MB).'];
        }

        // Trust the real MIME type, not the client filename.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime])) {
            return ['ok' => false, 'message' => 'Only JPG, PNG or WEBP images are allowed.'];
        }

        $dir = __DIR__ . '/../../public/assets/uploads/' . $subdir . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = $subdir . '_' . bin2hex(random_bytes(8)) . '.' . self::ALLOWED[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            return ['ok' => false, 'message' => 'Could not save the uploaded image.'];
        }

        return ['ok' => true, 'path' => '/assets/uploads/' . $subdir . '/' . $name];
    }
}
