<?php
namespace App\Repositories;

use App\Framework\Repository;
use App\Models\ContentBlockModel;

class ContentRepository extends Repository
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

    public function upsertBlock(string $pageSlug, string $blockKey, ?string $html, ?string $imagePath, ?int $updatedBy): void
    {
        $sql = 'INSERT INTO content_blocks (page_slug, block_key, html, image_path, updated_by)
                VALUES (:page_slug, :block_key, :html, :image_path, :updated_by)
                ON DUPLICATE KEY UPDATE
                    html = VALUES(html),
                    image_path = COALESCE(VALUES(image_path), image_path),
                    updated_by = VALUES(updated_by)';

        $this->execute($sql, [
            'page_slug'  => $pageSlug,
            'block_key'  => $blockKey,
            'html'       => $html,
            'image_path' => $imagePath,
            'updated_by' => $updatedBy,
        ]);
    }

    public function recordImage(string $path, ?string $alt, ?int $uploadedBy): void
    {
        $this->execute(
            'INSERT INTO images (path, alt, uploaded_by) VALUES (:path, :alt, :uploaded_by)',
            ['path' => $path, 'alt' => $alt, 'uploaded_by' => $uploadedBy]
        );
    }
}
