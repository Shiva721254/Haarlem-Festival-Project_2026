<?php
namespace App\Models;

use App\Enums\OrderStatus;

class OrderModel
{
    public int $id;
    public int $user_id;
    public OrderStatus $status;
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
        $o->fillOrder($data);
        $o->fillPayment($data);
        $o->fillAdminSummary($data);
        return $o;
    }

    private function fillOrder(array $data): void
    {
        $this->id = (int)$data['id'];
        $this->user_id = (int)$data['user_id'];
        $this->status = OrderStatus::from($data['status']);
        $this->invoice_number = $data['invoice_number'] ?? null;
        $this->subtotal = (float)$data['subtotal'];
        $this->vat_total = (float)$data['vat_total'];
        $this->total = (float)$data['total'];
    }

    private function fillPayment(array $data): void
    {
        $this->payment_intent_id = $data['payment_intent_id'] ?? null;
        $this->pay_later_until = $data['pay_later_until'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->paid_at = $data['paid_at'] ?? null;
    }

    private function fillAdminSummary(array $data): void
    {
        $this->customer_name = $data['customer_name'] ?? null;
        $this->customer_email = $data['customer_email'] ?? null;
        $this->item_count = isset($data['item_count']) ? (int)$data['item_count'] : 0;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    public function canPayLater(): bool
    {
        return $this->isPending()
            && $this->pay_later_until !== null
            && strtotime($this->pay_later_until) >= time();
    }
}
