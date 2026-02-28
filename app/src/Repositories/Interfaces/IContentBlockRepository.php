<?php
declare(strict_types=1);

namespace App\Repositories\Interfaces;

interface IContentBlockRepository
{
    public function getByPage(string $page): array;
    public function updateValue(int $id, string $value): void;
    public function getByPageSectionKey(string $page, string $section, string $keyName, int $sortOrder = 0): ?array;
    public function getByPageSectionKeyList(string $page, string $section, string $keyName): array;
}
