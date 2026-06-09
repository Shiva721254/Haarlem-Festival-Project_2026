<?php
namespace App\Models;

class OrderModel
{
    public int $id;
    public int $user_id;
    public string $status;            // pending|paid|failed|cancelled
    public ?string $invoice_number = null;
    public float $subtotal = 0.0;
    public float $vat_total = 0.0;
    public float $total = 0.0;
    public ?string $payment_intent_id = null;
    public ?string $pay_later_until = null;
    public ?string $created_at = null;
    public ?string $paid_at = null;

    // Optional admin/list display data.
    public ?string $customer_name = null;
    public ?string $customer_email = null;
    public int $item_count = 0;

    /** @var OrderItemModel[] */
    public array $items = [];

    public static function fromDb(array $data): self
    {
        $o = new self();
        $o->id = (int)$data['id'];
        $o->user_id = (int)$data['user_id'];
        $o->status = $data['status'];
        $o->invoice_number = $data['invoice_number'] ?? null;
        $o->subtotal = (float)$data['subtotal'];
        $o->vat_total = (float)$data['vat_total'];
        $o->total = (float)$data['total'];
        $o->payment_intent_id = $data['payment_intent_id'] ?? null;
        $o->pay_later_until = $data['pay_later_until'] ?? null;
        $o->created_at = $data['created_at'] ?? null;
        $o->paid_at = $data['paid_at'] ?? null;
        $o->customer_name = $data['customer_name'] ?? null;
        $o->customer_email = $data['customer_email'] ?? null;
        $o->item_count = isset($data['item_count']) ? (int)$data['item_count'] : 0;
        return $o;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
