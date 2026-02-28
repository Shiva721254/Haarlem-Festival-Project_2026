<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\ContentBlockModel;
use App\Repositories\Interfaces\IContentBlockRepository;
use App\Services\Interfaces\IContentBlockService;

final class ContentBlockService implements IContentBlockService
{
    private IContentBlockRepository $repo;

    public function __construct(IContentBlockRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getPageBlocks(string $page): array
    {
        return $this->repo->getByPage($page);
    }

    public function saveValues(array $values): void
    {
        foreach ($values as $id => $value) {
            $this->repo->updateValue((int)$id, (string)$value);
        }
    }

    public function getJson(string $page, string $section, string $keyName, int $sortOrder = 0): array
    {
        $row = $this->repo->getByPageSectionKey($page, $section, $keyName, $sortOrder);
        if (!$row) return [];

        $b = ContentBlockModel::fromArray($row);
        return $b->json();
    }

    public function getJsonList(string $page, string $section, string $keyName): array
    {
        $rows = $this->repo->getByPageSectionKeyList($page, $section, $keyName);
        $out = [];

        foreach ($rows as $row) {
            $b = ContentBlockModel::fromArray($row);
            $out[] = $b->json();
        }

        return $out;
    }
}
