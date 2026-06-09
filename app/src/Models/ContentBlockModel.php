<?php
namespace App\Models;

class ContentBlockModel
{
    public int $id = 0;
    public string $page_slug = '';
    public string $block_key = '';
    public ?string $html = null;
    public ?string $image_path = null;
    public ?int $updated_by = null;
    public ?string $updated_at = null;

    public static function fromDb(array $data): self
    {
        $block = new self();
        $block->id = (int)$data['id'];
        $block->page_slug = $data['page_slug'];
        $block->block_key = $data['block_key'];
        $block->html = $data['html'] ?? null;
        $block->image_path = $data['image_path'] ?? null;
        $block->updated_by = isset($data['updated_by']) ? (int)$data['updated_by'] : null;
        $block->updated_at = $data['updated_at'] ?? null;
        return $block;
    }
}
