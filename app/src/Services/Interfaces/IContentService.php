<?php
namespace App\Services\Interfaces;

use App\Models\ContentBlockModel;

interface IContentService
{
    /**
     * @return array<string,ContentBlockModel>
     */
    public function getPageBlocks(string $pageSlug): array;

    public function countForPage(string $pageSlug): int;

    /**
     * @param array<string,string> $htmlByBlock
     * @param array<string,array<string,mixed>> $filesByBlock
     */
    public function savePage(string $pageSlug, array $htmlByBlock, array $filesByBlock, ?int $updatedBy): void;

    /**
     * @param array<string,string> $htmlByBlock
     * @param array<string,mixed> $uploadField Raw $_FILES entry for images[block_key].
     */
    public function savePageFromUploadField(string $pageSlug, array $htmlByBlock, array $uploadField, ?int $updatedBy): void;
}
