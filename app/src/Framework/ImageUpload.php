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
        return self::handleFile($_FILES[$field], $subdir);
    }

    public static function handleFile(array $file, string $subdir, int $maxBytes = self::MAX_BYTES, ?string $prefix = null): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true];
        }
        $error = self::validationError($file, $maxBytes);
        if ($error !== null) {
            return ['ok' => false, 'message' => $error];
        }
        return self::store($file, $subdir, $prefix ?? $subdir);
    }

    private static function validationError(array $file, int $maxBytes): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Image upload failed; please try again.';
        }
        if ($file['size'] > $maxBytes) {
            return 'Image is too large (max ' . (int)($maxBytes / 1024 / 1024) . ' MB).';
        }
        return isset(self::ALLOWED[self::mime($file)]) ? null : 'Only JPG, PNG or WEBP images are allowed.';
    }

    private static function store(array $file, string $subdir, string $prefix): array
    {
        $dir = self::uploadDir($subdir);
        $name = self::safePrefix($prefix) . '_' . bin2hex(random_bytes(8)) . '.' . self::ALLOWED[self::mime($file)];
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            return ['ok' => false, 'message' => 'Could not save the uploaded image.'];
        }
        return ['ok' => true, 'path' => '/assets/uploads/' . $subdir . '/' . $name];
    }

    private static function safePrefix(string $prefix): string
    {
        return preg_replace('/[^a-z0-9-]/', '-', strtolower($prefix)) ?: 'upload';
    }

    private static function uploadDir(string $subdir): string
    {
        $dir = __DIR__ . '/../../public/assets/uploads/' . $subdir . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private static function mime(array $file): string
    {
        return (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    }

    /**
     * Resolve an optional admin-form upload into a stored path and/or an error,
     * so callers don't repeat the same path/error branching.
     *
     * @return array{path:?string,error:?string}
     */
    public static function resolve(string $field, string $subdir): array
    {
        $upload = self::handle($field, $subdir);
        $path = $upload['path'] ?? null;
        return [
            'path'  => $path,
            'error' => ($path === null && !$upload['ok']) ? $upload['message'] : null,
        ];
    }
}
