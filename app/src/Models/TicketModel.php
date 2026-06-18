<?php
namespace App\Models;

use App\Enums\TicketStatus;

class TicketModel
{
    public int $id;
    public int $order_item_id;
    public string $qr_code;
    public TicketStatus $status;
    public ?string $scanned_at = null;

    public static function fromDb(array $data): self
    {
        $t = new self();
        $t->id = (int)$data['id'];
        $t->order_item_id = (int)$data['order_item_id'];
        $t->qr_code = $data['qr_code'];
        $t->status = TicketStatus::from($data['status']);
        $t->scanned_at = $data['scanned_at'] ?? null;
        return $t;
    }
}
