<?php
namespace App\Repositories\Interfaces;

use App\Models\ContentBlockModel;

interface IContentRepository
{
    /** @return array<string,ContentBlockModel> */
    public function getBlocksByPage(string $pageSlug): array;

    public function countForPage(string $pageSlug): int;

    public function upsertBlock(string $pageSlug, string $blockKey, ?string $html, ?string $imagePath, ?int $updatedBy): void;

    public function recordImage(string $path, ?string $alt, ?int $uploadedBy): void;
}
