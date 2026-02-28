<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Framework\Repository;
use App\Models\ContentBlockModel;
use App\Repositories\Interfaces\IContentBlockRepository;
use PDO;

final class ContentBlockRepository extends Repository implements IContentBlockRepository
{
    public function getByPage(string $page): array
    {
        $sql = "
            SELECT id, page, section, key_name, value, type, sort_order
            FROM content_blocks
            WHERE page = :page
            ORDER BY section ASC, sort_order ASC, key_name ASC
        ";

        $db = $this->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([':page' => $page]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $out = [];

        foreach ($rows as $row) {
            $out[] = ContentBlockModel::fromDb($row);
        }

        return $out;
    }

    public function updateValue(int $id, string $value): void
    {
        $sql = "UPDATE content_blocks SET value = :value WHERE id = :id";

        $db = $this->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':value' => $value,
            ':id' => $id,
        ]);
    }

    public function getByPageSectionKey(string $page, string $section, string $keyName, int $sortOrder = 0): ?array
    {
        $sql = "
            SELECT id, page, section, key_name, value, type, sort_order
            FROM content_blocks
            WHERE page = :page AND section = :section AND key_name = :key_name AND sort_order = :sort_order
            LIMIT 1
        ";

        $db = $this->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':page' => $page,
            ':section' => $section,
            ':key_name' => $keyName,
            ':sort_order' => $sortOrder,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getByPageSectionKeyList(string $page, string $section, string $keyName): array
    {
        $sql = "
            SELECT id, page, section, key_name, value, type, sort_order
            FROM content_blocks
            WHERE page = :page AND section = :section AND key_name = :key_name
            ORDER BY sort_order ASC
        ";

        $db = $this->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':page' => $page,
            ':section' => $section,
            ':key_name' => $keyName,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
