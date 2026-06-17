<?php
namespace App\Services;

use App\Framework\ImageUpload;
use App\Models\ContentBlockModel;
use App\Repositories\ContentRepository;
use App\Repositories\Interfaces\IContentRepository;
use App\Services\Interfaces\IContentService;

class ContentService implements IContentService
{
    private const MAX_IMAGE_BYTES = 3 * 1024 * 1024;

    /** Fallback content shown on the homepage before an admin edits the blocks. */
    private const HOME_DEFAULTS = [
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

    private IContentRepository $repo;

    public function __construct(IContentRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @return array<string,ContentBlockModel>
     */
    public function getPageBlocks(string $pageSlug): array
    {
        return $this->withDefaults($pageSlug, $this->repo->getBlocksByPage($pageSlug));
    }

    public function countForPage(string $pageSlug): int
    {
        return $this->repo->countForPage($pageSlug);
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

    /**
     * @param array<string,string> $htmlByBlock
     * @param array<string,mixed> $uploadField Raw $_FILES entry for images[block_key].
     */
    public function savePageFromUploadField(string $pageSlug, array $htmlByBlock, array $uploadField, ?int $updatedBy): void
    {
        $this->savePage($pageSlug, $htmlByBlock, $this->groupUploadedFiles($uploadField), $updatedBy);
    }

    /**
     * @param array<string,mixed> $uploadField
     * @return array<string,array<string,mixed>>
     */
    private function groupUploadedFiles(array $uploadField): array
    {
        if (empty($uploadField) || !isset($uploadField['name']) || !is_array($uploadField['name'])) {
            return [];
        }
        $files = [];
        foreach ($uploadField['name'] as $key => $name) {
            $files[$key] = $this->fileEntry($uploadField, $key, $name);
        }
        return $files;
    }

    /** @return array<string,mixed> a single normalised $_FILES entry */
    private function fileEntry(array $uploadField, int|string $key, string $name): array
    {
        return [
            'name' => $name,
            'type' => $uploadField['type'][$key] ?? '',
            'tmp_name' => $uploadField['tmp_name'][$key] ?? '',
            'error' => $uploadField['error'][$key] ?? UPLOAD_ERR_NO_FILE,
            'size' => $uploadField['size'][$key] ?? 0,
        ];
    }

    private function handleUpload(?array $file, string $blockKey, ?int $uploadedBy): ?string
    {
        $upload = $file === null ? ['ok' => true] : ImageUpload::handleFile($file, 'cms', self::MAX_IMAGE_BYTES, $blockKey);
        if (!$upload['ok']) {
            throw new \RuntimeException($upload['message']);
        }
        if (!isset($upload['path'])) {
            return null;
        }
        $this->repo->recordImage($upload['path'], $blockKey, $uploadedBy);
        return $upload['path'];
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
        foreach (self::HOME_DEFAULTS as $key => $data) {
            $blocks[$key] ??= $this->defaultBlock($pageSlug, $key, $data);
        }
        return $blocks;
    }

    /** @param array{html:string,image_path:?string} $data */
    private function defaultBlock(string $pageSlug, string $key, array $data): ContentBlockModel
    {
        $block = new ContentBlockModel();
        $block->page_slug = $pageSlug;
        $block->block_key = $key;
        $block->html = $data['html'];
        $block->image_path = $data['image_path'];
        return $block;
    }
}
