<?php
declare(strict_types=1);

namespace App\Services\Interfaces;

interface IContentBlockService
{
    public function getPageBlocks(string $page): array;
    public function saveValues(array $values): void;

    public function getJson(string $page, string $section, string $keyName, int $sortOrder = 0): array;
    public function getJsonList(string $page, string $section, string $keyName): array;
}
