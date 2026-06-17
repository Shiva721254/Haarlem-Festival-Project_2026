<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Models\ContentBlockModel;
use App\Repositories\Interfaces\IContentRepository;

class ContentRepository extends Repository implements IContentRepository
{
    /**
     * @return array<string,ContentBlockModel>
     */
    public function getBlocksByPage(string $pageSlug): array
    {
        $rows = $this->fetchAll(
            'SELECT * FROM content_blocks WHERE page_slug = :page ORDER BY block_key',
            ['page' => $pageSlug]
        );

        $blocks = [];
        foreach ($rows as $row) {
            $block = ContentBlockModel::fromDb($row);
            $blocks[$block->block_key] = $block;
        }
        return $blocks;
    }

    public function countForPage(string $pageSlug): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS n FROM content_blocks WHERE page_slug = :page', ['page' => $pageSlug]);
        return (int) ($row['n'] ?? 0);
    }

    public function upsertBlock(string $pageSlug, string $blockKey, ?string $html, ?string $imagePath, ?int $updatedBy): void
    {
        $this->execute($this->upsertBlockSql(), [
            'page_slug'  => $pageSlug,
            'block_key'  => $blockKey,
            'html'       => $html,
            'image_path' => $imagePath,
            'updated_by' => $updatedBy,
        ]);
    }

    private function upsertBlockSql(): string
    {
        return 'INSERT INTO content_blocks (page_slug, block_key, html, image_path, updated_by)
                VALUES (:page_slug, :block_key, :html, :image_path, :updated_by)
                ON DUPLICATE KEY UPDATE html = VALUES(html),
                    image_path = COALESCE(VALUES(image_path), image_path), updated_by = VALUES(updated_by)';
    }

    public function recordImage(string $path, ?string $alt, ?int $uploadedBy): void
    {
        $this->execute(
            'INSERT INTO images (path, alt, uploaded_by) VALUES (:path, :alt, :uploaded_by)',
            ['path' => $path, 'alt' => $alt, 'uploaded_by' => $uploadedBy]
        );
    }
}
