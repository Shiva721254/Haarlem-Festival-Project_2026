<?php
declare(strict_types=1);

namespace App\Models;

class ContentBlockModel
{
    public int $Id = 0;
    public string $Page = '';
    public string $Section = '';
    public string $KeyName = '';
    public string $Value = '';
    public string $Type = 'text';
    public int $SortOrder = 0;

    public static function fromDb(array $data): self
    {
        return self::fromArray($data);
    }

    public static function fromArray(array $data): self
    {
        $b = new self();

        $b->Id = (int)($data['id'] ?? 0);
        $b->Page = (string)($data['page'] ?? '');
        $b->Section = (string)($data['section'] ?? '');
        $b->KeyName = (string)($data['key_name'] ?? '');
        $b->Value = (string)($data['value'] ?? '');
        $b->Type = (string)($data['type'] ?? 'text');
        $b->SortOrder = (int)($data['sort_order'] ?? 0);

        return $b;
    }

    public function json(): array
    {
        if ($this->Type !== 'json') return [];
        $decoded = json_decode($this->Value, true);
        return is_array($decoded) ? $decoded : [];
    }
}
