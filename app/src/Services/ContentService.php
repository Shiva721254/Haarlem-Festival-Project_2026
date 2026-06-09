<?php
namespace App\Services;

use App\Models\ContentBlockModel;
use App\Repositories\ContentRepository;

class ContentService
{
    private const CMS_UPLOAD_DIR = __DIR__ . '/../../public/assets/uploads/cms/';
    private const CMS_UPLOAD_PUBLIC = '/assets/uploads/cms/';
    private const MAX_IMAGE_BYTES = 3 * 1024 * 1024;
    private const ALLOWED_IMAGES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    private ContentRepository $repo;

    public function __construct()
    {
        $this->repo = new ContentRepository();
    }

    /**
     * @return array<string,ContentBlockModel>
     */
    public function getPageBlocks(string $pageSlug): array
    {
        return $this->withDefaults($pageSlug, $this->repo->getBlocksByPage($pageSlug));
    }

    /**
     * @param array<string,string> $htmlByBlock
     * @param array<string,array<string,mixed>> $filesByBlock
     */
    public function savePage(string $pageSlug, array $htmlByBlock, array $filesByBlock, ?int $updatedBy): void
    {
        foreach ($htmlByBlock as $blockKey => $html) {
            $imagePath = $this->handleUpload($filesByBlock[$blockKey] ?? null, $blockKey, $updatedBy);
            $this->repo->upsertBlock($pageSlug, $blockKey, $this->cleanHtml($html), $imagePath, $updatedBy);
        }
    }

    private function handleUpload(?array $file, string $blockKey, ?int $uploadedBy): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Image upload failed.');
        }
        if (($file['size'] ?? 0) > self::MAX_IMAGE_BYTES) {
            throw new \RuntimeException('Image is too large. Maximum size is 3 MB.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED_IMAGES[$mime])) {
            throw new \RuntimeException('Only JPG, PNG, or WEBP images are allowed.');
        }
        if (!is_dir(self::CMS_UPLOAD_DIR)) {
            mkdir(self::CMS_UPLOAD_DIR, 0775, true);
        }

        $filename = preg_replace('/[^a-z0-9-]/', '-', strtolower($blockKey)) . '-' . bin2hex(random_bytes(8)) . '.' . self::ALLOWED_IMAGES[$mime];
        $target = self::CMS_UPLOAD_DIR . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new \RuntimeException('Could not save uploaded image.');
        }

        $path = self::CMS_UPLOAD_PUBLIC . $filename;
        $this->repo->recordImage($path, $blockKey, $uploadedBy);
        return $path;
    }

    private function cleanHtml(string $html): string
    {
        $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><a><span>';
        $clean = strip_tags($html, $allowed);
        return trim($clean);
    }

    /**
     * @param array<string,ContentBlockModel> $blocks
     * @return array<string,ContentBlockModel>
     */
    private function withDefaults(string $pageSlug, array $blocks): array
    {
        if ($pageSlug !== 'home') {
            return $blocks;
        }

        $defaults = [
            'hero' => [
                'html' => '<h1>Welcome to Haarlem Festival</h1><p>Discover music, food, history, and culture across the city.</p>',
                'image_path' => '/assets/images/haarlem-homepage-hero.jpeg',
            ],
            'intro' => [
                'html' => '<h2>Festival highlights</h2><p>Browse the programme and reserve tickets for your favourite events.</p>',
                'image_path' => null,
            ],
            'practical' => [
                'html' => '<h2>Plan your visit</h2><p>Create an account to manage your tickets and personal programme.</p>',
                'image_path' => null,
            ],
        ];

        foreach ($defaults as $key => $data) {
            if (!isset($blocks[$key])) {
                $block = new ContentBlockModel();
                $block->page_slug = $pageSlug;
                $block->block_key = $key;
                $block->html = $data['html'];
                $block->image_path = $data['image_path'];
                $blocks[$key] = $block;
            }
        }
        return $blocks;
    }
}
